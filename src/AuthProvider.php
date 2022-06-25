<?php declare(strict_types=1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallenge;
use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallengeResponse;
use CEmerson\Auth\AuthResponse\AuthResponse;

interface AuthProvider
{
    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse;

    public function respondToAuthenticationChallenge(
        AuthChallengeResponse $authenticationChallengeResponse
    ): AuthResponse;

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool;

    public function forgotPassword(string $username);

    public function registerUser(string $username, string $password);
}
