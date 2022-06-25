<?php

namespace CEmerson\Auth;

use CEmerson\Auth\AuthenticationParameters;
use CEmerson\Auth\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallenge;
use CEmerson\Auth\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\AuthenticationResponse\AuthenticationResponse;

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
