<?php

declare(strict_types=1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthenticationFailed;
use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponses\AuthFailedResponse;
use CEmerson\Auth\AuthResponse;
use CEmerson\Auth\AuthResponses\UserNotFoundResponse;
use Psr\Log\LoggerInterface;

final class Auth
{
    private AuthContext $authContext;
    private LoggerInterface $logger;
    private AuthProvider $provider;

    public function __construct(
        AuthContext $authContext,
        LoggerInterface $logger,
        AuthProvider $authProvider
    ) {
        $this->authContext = $authContext;
        $this->logger = $logger;

        $this->provider = $authProvider;
    }

    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse
    {
        $this->logger->info("Attempting authentication with provider {provider}", [
            'provider' => get_class($this->provider)
        ]);

        $authResponse = $this->provider->attemptAuthentication($authParameters);

        if ($authResponse instanceof AuthSucceededResponse) {
            $this->logger->info("Authentication succeeded!");

            $this->authContext->saveSessionInfo($authResponse->getSessionInfo());

            if ($authParameters->getRememberMe()) {
                $this->authContext->saveRememberedLoginInfo($authResponse->getRememberedLoginInfo());
            }

            return $authResponse;
        } elseif ($authResponse instanceof UserNotFoundResponse) {
            $this->logger->info("Authentication result - User Not Found.");
        } elseif ($authResponse instanceof AuthFailedResponse) {
            $this->logger->info("Authentication failed - {response}", [
                'response' => get_class($authResponse)
            ]);

            throw new AuthenticationFailed($authResponse);
        } else {
            $this->logger->info("Authentication response - {response}", [
                'response' => get_class($authResponse)
            ]);

            return $authResponse;
        }

        $this->logger->info("User was not found after trying all providers.");

        throw new UserNotFound($authResponse);
    }

    public function respondToChallenge(AuthChallengeResponse $challengeResponse): AuthResponse
    {
        return $this->provider->respondToAuthenticationChallenge($challengeResponse);
    }

    public function logout()
    {
        $this->provider->logout();
        $this->authContext->deleteSessionInfo();
        $this->authContext->deleteRememberedLogin();
    }

    public function isLoggedIn(): bool
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        return $this->session->userIsLoggedIn();
    }

    public function getCurrentUser(): string
    {
        return "Unknown user";

        if (!$this->isLoggedIn()) {
            throw new NoUserLoggedIn();
        }
    }

    public function hasAuthenticatedThisSession(): bool
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        return $this->session->userHasAuthenticatedThisSession();
    }

    public function reAuthenticateCurrentUser(AuthParameters $authParameters): AuthResponse
    {
        $currentUser = $this->getCurrentUser();

        return $this->handleUserAuthentication($currentUser, $password, true);
    }

    public function cleanup()
    {
        $this->rememberedLoginService->cleanupExpiredRememberedLogins();
    }
}
