<?php declare(strict_types=1);

namespace CEmerson\Auth\Providers\Local;

use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallengeResponse;
use CEmerson\Auth\AuthResponse\AuthResponse;
use CEmerson\Auth\AuthProvider;

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

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool
    {
        // TODO: Implement changePassword() method.
    }

    public function forgotPassword(string $username)
    {
        // TODO: Implement forgotPassword() method.
    }

    public function registerUser(string $username, string $password)
    {
        // TODO: Implement registerUser() method.
    }
}
