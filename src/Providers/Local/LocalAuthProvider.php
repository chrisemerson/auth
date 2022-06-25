<?php

namespace CEmerson\Auth\Providers\Local;

use CEmerson\Auth\AuthenticationParameters;
use CEmerson\Auth\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\AuthenticationResponse\AuthenticationResponse;
use CEmerson\Auth\AuthProvider;

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
