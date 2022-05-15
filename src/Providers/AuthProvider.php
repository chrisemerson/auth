<?php

namespace CEmerson\Auth\Providers;

use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallenge;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationResponse;

interface AuthProvider
{
    public function attemptAuthentication(AuthenticationParameters $authParameters): AuthenticationResponse;

    public function respondToAuthenticationChallenge(
        AuthenticationChallengeResponse $authenticationChallengeResponse
    ): AuthenticationResponse;

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool;

    public function forgotPassword(string $username);

    public function registerUser(string $username, string $password);
}
