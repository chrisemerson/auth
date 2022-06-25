<?php

namespace CEmerson\Auth\AuthenticationResponse\AuthenticationChallenge;

use CEmerson\Auth\AuthenticationResponse\AuthenticationResponse;
use JsonSerializable;

interface AuthenticationChallenge extends AuthenticationResponse, JsonSerializable
{
    public function getId(): string;

    public static function fromJson(string $json): self;

    public function createChallengeResponse(string $response): AuthenticationChallengeResponse;
}
