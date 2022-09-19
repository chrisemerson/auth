<?php

declare(strict_types=1);

namespace CEmerson\Auth;

use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponse;
use CEmerson\Auth\User\DefaultAuthUser;

interface AuthProvider
{
    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse;

    public function respondToAuthenticationChallenge(
        string $authenticationChallengeName,
        string $authenticationChallengeDetails,
        string $authenticationChallengeResponse
    ): AuthResponse;

    public function changePassword(array $sessionInfo, string $oldPassword, string $newPassword): bool;

    public function forgotPassword(string $username);

    public function resetForgottenPassword(string $username, string $confirmationCode, string $newPassword);

    public function registerUser(string $username, ?string $password = null, array $extraUserAttributes = []);

    public function resendTemporaryPassword(string $username);

    public function disableUser(string $username);

    public function enableUser(string $username);

    public function logout();

    public function isSessionValid(array $sessionInfo): bool;

    public function getCurrentUser(array $sessionInfo): DefaultAuthUser;

    public function refreshSessionTokens(array $sessionInfo, array $rememberedLoginInfo): array;
}
