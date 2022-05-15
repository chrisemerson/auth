<?php

namespace CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\PasswordResetRequired;

use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallenge;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\PasswordResetRequired\PasswordResetRequiredChallengeResponse;

class PasswordResetRequiredChallenge implements AuthenticationChallenge
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

    public function createChallengeResponse(string $response): AuthenticationChallengeResponse
    {
        return new PasswordResetRequiredChallengeResponse(
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
