<?php

declare(strict_types=1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponse;

interface AuthProvider
{
    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse;

    public function respondToAuthenticationChallenge(
        AuthChallengeResponse $authenticationChallengeResponse
    ): AuthResponse;

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool;

    public function forgotPassword(string $username);

    public function registerUser(string $username, string $password);

    public function logout();

    public function isSessionValid(array $sessionInfo): bool;

    public function refreshSessionFromRememberedLoginInfo(array $rememberedLoginInfo): array;
}
