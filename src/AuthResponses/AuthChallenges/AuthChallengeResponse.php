<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthResponses\AuthChallenges;

interface AuthChallengeResponse
{
    public function getChallengeName(): string;

    public function getChallengeId(): string;

    public function getUsername(): string;

    public function getChallengeParameters(): array;

    public function isSecretHashRequired(): bool;
}
