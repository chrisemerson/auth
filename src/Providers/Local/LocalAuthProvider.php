<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\Local;

use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponse;
use CEmerson\Auth\AuthProvider;
use CEmerson\Auth\User\DefaultAuthUser;

class LocalAuthProvider implements AuthProvider
{
    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse
    {
        // TODO: Implement attemptAuthentication() method.
    }

    public function respondToAuthenticationChallenge(
        AuthChallengeResponse $authenticationChallengeResponse
    ): AuthResponse {
        // TODO: Implement respondToAuthenticationChallenge() method.
    }

    public function changePassword(array $sessionInfo, string $currentPassword, string $newPassword): bool
    {
        // TODO: Implement changePassword() method.
    }

    public function forgotPassword(string $username)
    {
        // TODO: Implement forgotPassword() method.
    }

    public function resetForgottenPassword(string $username, string $confirmationCode, string $newPassword)
    {
        // TODO: Implement resetForgottenPassword() method.
    }

    public function registerUser(string $username, string $password)
    {
        // TODO: Implement registerUser() method.
    }

    public function logout()
    {
        // TODO: Implement logout() method.
    }

    public function isSessionValid(array $sessionInfo): bool
    {
        // TODO: Implement isSessionValid() method.
    }

    public function getCurrentUser(array $sessionInfo): DefaultAuthUser
    {
        // TODO: Implement getCurrentUser() method.
    }

    public function refreshSessionTokens(array $sessionInfo, array $rememberedLoginInfo): array
    {
        // TODO: Implement refreshSessionTokens() method.
    }
}
