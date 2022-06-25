<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\AuthChallenge\MFARequired;

use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallenge;
use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallengeResponse;

class MFARequiredChallenge implements AuthChallenge
{
    public function getId(): string
    {
        // TODO: Implement getId() method.
    }

    public static function fromJson(string $json): AuthChallenge
    {
        // TODO: Implement fromJson() method.
    }

    public function createChallengeResponse(string $response): AuthChallengeResponse
    {
        // TODO: Implement createChallengeResponse() method.
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
