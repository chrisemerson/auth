<?php

declare(strict_types=1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthFailed;
use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Providers\AwsCognito\AwsCognitoAuthSucceededResponse;
use CEmerson\Auth\User\AuthUser;
use CEmerson\Auth\User\AuthUserFactory;
use Exception;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Psr\Log\LoggerInterface;

final class Auth
{
    private AuthProvider $provider;
    private AuthContext $authContext;
    private AuthUserFactory $authUserFactory;
    private LoggerInterface $logger;

    public function __construct(
        AuthProvider $authProvider,
        AuthContext $authContext,
        AuthUserFactory $authUserFactory,
        LoggerInterface $logger
    ) {
        $this->provider = $authProvider;
        $this->authContext = $authContext;
        $this->authUserFactory = $authUserFactory;
        $this->logger = $logger;

        $this->refreshTokens();
    }

    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse
    {
        $this->logger->info("Attempting authentication with provider {provider}", [
            'provider' => basename(get_class($this->provider))
        ]);

        $authResponse = $this->provider->attemptAuthentication($authParameters);

        $this->processAuthenticationSucceededResponse($authResponse, $authParameters->getRememberMe());

        if ($authResponse instanceof AuthSucceededResponse || $authResponse instanceof AuthChallenge) {
            return $authResponse;
        }

        $this->logger->info("Authentication failed - {response}", [
            'response' => get_class($authResponse)
        ]);

        throw new AuthFailed($authResponse);
    }

    public function respondToChallenge(
        string $challengeName,
        string $challengeDetails,
        string $challengeResponse,
        bool $rememberMe = false
    ): AuthResponse {
        $response = $this->provider->respondToAuthenticationChallenge(
            $challengeName,
            $challengeDetails,
            $challengeResponse
        );

        $this->processAuthenticationSucceededResponse($response, $rememberMe);

        return $response;
    }

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool
    {
        $this->provider->changePassword($username, $oldPassword, $newPassword);
    }

    public function forgotPassword(string $username)
    {
        $this->provider->forgotPassword($username);
    }

    public function resetForgottenPassword(string $username, string $confirmationCode, string $newPassword)
    {
        $this->provider->resetForgottenPassword($username, $confirmationCode, $newPassword);
    }

    public function registerUser(string $username, string $password)
    {
        $this->provider->registerUser($username, $password);
    }

    public function logout()
    {
        $this->provider->logout();

        $this->authContext->deleteSessionInfo();
        $this->authContext->deleteRememberedLoginInfo();
    }

    public function isLoggedIn(): bool
    {
        if (!$this->provider->isSessionValid($this->authContext->getSessionInfo())) {
            $this->authContext->deleteSessionInfo();
            $this->refreshTokens();
        }

        return $this->provider->isSessionValid($this->authContext->getSessionInfo());
    }

    public function getCurrentUser(): AuthUser
    {
        if (!$this->isLoggedIn()) {
            throw new NoUserLoggedIn();
        }

        return $this->authUserFactory->getAuthUser(
            $this->provider->getCurrentUser($this->authContext->getSessionInfo())
        );
    }

    public function hasAuthenticatedThisSession(): bool
    {
        return false;
    }

    public function reAuthenticateCurrentUser(AuthParameters $authParameters): AuthResponse
    {
        return false;
    }

    private function processAuthenticationSucceededResponse(AuthResponse $authResponse, bool $rememberMe = false)
    {
        if ($authResponse instanceof AuthSucceededResponse) {
            $this->logger->info("Authentication succeeded!");

            $this->authContext->saveSessionInfo($authResponse->getSessionInfo());

            if ($rememberMe) {
                $this->authContext->saveRememberedLoginInfo($authResponse->getRememberedLoginInfo());
            }
        }
    }

    private function refreshTokens()
    {
        $newSessionInfo = $this->provider->refreshSessionTokens(
            $this->authContext->getSessionInfo(),
            $this->authContext->getRememberedLoginInfo()
        );

        if (empty($newSessionInfo)) {
            $this->authContext->deleteSessionInfo();
            $this->authContext->deleteRememberedLoginInfo();
        } else {
            $this->authContext->saveSessionInfo($newSessionInfo);
        }
    }
}
