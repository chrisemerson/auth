<?php

declare(strict_types=1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthFailed;
use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Providers\AwsCognito\AwsCognitoAuthSucceededResponse;
use Exception;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Psr\Log\LoggerInterface;

final class Auth
{
    private AuthContext $authContext;
    private LoggerInterface $logger;
    private AuthProvider $provider;

    public function __construct(
        AuthProvider $authProvider,
        AuthContext $authContext,
        LoggerInterface $logger
    ) {
        $this->authContext = $authContext;
        $this->logger = $logger;
        $this->provider = $authProvider;

        $this->attemptToLoadAuthStatusFromRememberedLoginInfo();
    }

    public function attemptAuthentication(AuthParameters $authParameters): bool
    {
        $this->logger->info("Attempting authentication with provider {provider}", [
            'provider' => basename(get_class($this->provider))
        ]);

        $authResponse = $this->provider->attemptAuthentication($authParameters);

        if ($authResponse instanceof AuthSucceededResponse) {
            $this->logger->info("Authentication succeeded!");

            $this->authContext->saveSessionInfo($authResponse->getSessionInfo());

            if ($authParameters->getRememberMe()) {
                $this->authContext->saveRememberedLoginInfo($authResponse->getRememberedLoginInfo());
            }

            return true;
        }

        $this->logger->info("Authentication failed - {response}", [
            'response' => get_class($authResponse)
        ]);

        throw new AuthFailed($authResponse);
    }

    public function respondToChallenge(AuthChallengeResponse $challengeResponse): AuthResponse
    {
        return $this->provider->respondToAuthenticationChallenge($challengeResponse);
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
            $this->attemptToLoadAuthStatusFromRememberedLoginInfo();
        }

        return $this->provider->isSessionValid($this->authContext->getSessionInfo());
    }

    public function getCurrentUsername(): string
    {
        if (!$this->isLoggedIn()) {
            throw new NoUserLoggedIn();
        }

        $idToken = $this->authContext->getSessionInfo()[AwsCognitoAuthSucceededResponse::ID_TOKEN_KEY_NAME];

        $unencryptedToken = (new Parser(new JoseEncoder()))->parse($idToken);

        return $unencryptedToken->claims()->get('cognito:username');
    }

    public function hasAuthenticatedThisSession(): bool
    {
        return false;
    }

    public function reAuthenticateCurrentUser(AuthParameters $authParameters): AuthResponse
    {
    }

    private function attemptToLoadAuthStatusFromRememberedLoginInfo()
    {
        try {
            $sessionInfo = $this->provider->refreshSessionFromRememberedLoginInfo(
                $this->authContext->getRememberedLoginInfo()
            );

            $this->authContext->saveSessionInfo($sessionInfo);
        } catch (Exception $e) {
            //no op
        }
    }
}
