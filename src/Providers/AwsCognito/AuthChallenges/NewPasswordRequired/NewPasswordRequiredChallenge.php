<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\AuthChallenges\NewPasswordRequired;

use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;

class NewPasswordRequiredChallenge implements AuthChallenge
{
    private string $challengeId;
    private string $username;

    private const CHALLENGE_ID_KEY = 'challengeId';
    private const USERNAME_KEY = 'username';

    public function __construct(string $challengeId, string $username)
    {
        $this->challengeId = $challengeId;
        $this->username = $username;
    }

    public static function fromJson(string $json): self
    {
        $details = json_decode($json, true);

        return new self($details[self::CHALLENGE_ID_KEY], $details[self::USERNAME_KEY]);
    }

    public function createChallengeResponse(string $response): AuthChallengeResponse
    {
        return new NewPasswordRequiredChallengeResponse(
            $this->challengeId,
            $this->username,
            $response
        );
    }

    public function jsonSerialize()
    {
        return [
            self::CHALLENGE_ID_KEY => $this->challengeId,
            self::USERNAME_KEY => $this->username
        ];
    }
}
