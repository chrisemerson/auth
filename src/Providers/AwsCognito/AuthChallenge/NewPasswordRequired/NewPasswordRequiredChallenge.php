<?php

namespace CEmerson\Auth\Providers\AwsCognito\AuthChallenge\NewPasswordRequired;

use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallenge;
use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallengeResponse;

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
        $details = json_decode($json);

        return new self($details[self::CHALLENGE_ID_KEY], $details[self::USERNAME_KEY]);
    }

    public function getId(): string
    {
        return $this->challengeId;
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
        return json_encode([
            self::CHALLENGE_ID_KEY => $this->challengeId,
            self::USERNAME_KEY => $this->username
        ]);
    }
}
