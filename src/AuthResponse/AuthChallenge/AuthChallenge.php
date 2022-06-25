<?php declare(strict_types=1);

namespace CEmerson\Auth\AuthResponse\AuthChallenge;

use CEmerson\Auth\AuthResponse\AuthResponse;
use JsonSerializable;

interface AuthChallenge extends AuthResponse, JsonSerializable
{
    public function getId(): string;

    public static function fromJson(string $json): self;

    public function createChallengeResponse(string $response): AuthChallengeResponse;
}