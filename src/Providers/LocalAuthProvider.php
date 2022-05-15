<?php

namespace CEmerson\Auth\Providers;

use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationResponse;

class LocalAuthProvider implements AuthProvider
{
    public function attemptAuthentication(AuthenticationParameters $authParameters): AuthenticationResponse
    {
        // TODO: Implement attemptAuthentication() method.
    }

    public function respondToAuthenticationChallenge(AuthenticationChallengeResponse $authenticationChallengeResponse
    ): AuthenticationResponse {
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
