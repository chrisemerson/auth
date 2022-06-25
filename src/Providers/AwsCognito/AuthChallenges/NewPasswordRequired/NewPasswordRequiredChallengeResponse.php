<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\AuthChallenges\NewPasswordRequired;

use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;

class NewPasswordRequiredChallengeResponse implements AuthChallengeResponse
{
    private string $challengeId;
    private string $username;
    private string $newPassword;

    public function __construct(string $challengeId, string $username, string $newPassword)
    {
        $this->challengeId = $challengeId;
        $this->username = $username;
        $this->newPassword = $newPassword;
    }

    public function getChallengeName(): string
    {
        return 'NEW_PASSWORD_REQUIRED';
    }

    public function getChallengeId(): string
    {
        return $this->challengeId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getChallengeParameters(): array
    {
        return [
            'NEW_PASSWORD' => $this->newPassword,
            'USERNAME' => $this->username
        ];
    }

    public function isSecretHashRequired(): bool
    {
        return true;
    }
}
