<?php declare(strict_types = 1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\Exceptions\AuthenticationFailed;
use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\AuthenticationResponse\AuthenticationFailedResponse;
use CEmerson\Auth\AuthenticationResponse\AuthenticationResponse;
use CEmerson\Auth\AuthenticationResponse\UserNotFoundResponse;
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

    public function attemptAuthentication(AuthenticationParameters $authenticationParameters): AuthenticationResponse
    {
        $this->logger->info("Attempting authentication with provider {provider}", [
            'provider' => get_class($this->provider)
        ]);

        $authResponse = $this->provider->attemptAuthentication($authenticationParameters);

        if ($authResponse instanceof UserNotFoundResponse) {
            $this->logger->info("Authentication result - User Not Found. Skipping to next provider.");
        } elseif ($authResponse instanceof AuthenticationFailedResponse) {
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

    public function respondToChallenge(AuthenticationChallengeResponse $challengeResponse): AuthenticationResponse
    {
        return $this->provider->respondToAuthenticationChallenge($challengeResponse);
    }

    public function logout()
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        $this->session->deleteAuthSessionInfo();
        $this->rememberedLoginService->deleteRememberedLogin();
    }

    public function isLoggedIn(): bool
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        return $this->session->userIsLoggedIn();
    }

    public function getCurrentUser(): string
    {
        if (!$this->isLoggedIn()) {
            throw new NoUserLoggedIn();
        }

        return $this->session->getLoggedInUsername();
    }

    public function hasAuthenticatedThisSession(): bool
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        return $this->session->userHasAuthenticatedThisSession();
    }

    public function reAuthenticateCurrentUser(AuthenticationParameters $authenticationParameters): AuthenticationResponse
    {
        $currentUser = $this->getCurrentUser();

        return $this->handleUserAuthentication($currentUser, $password, true);
    }

    public function cleanup()
    {
        $this->rememberedLoginService->cleanupExpiredRememberedLogins();
    }
}
