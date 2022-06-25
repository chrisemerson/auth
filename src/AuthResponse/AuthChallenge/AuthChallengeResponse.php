<?php

namespace CEmerson\Auth\AuthResponse\AuthChallenge;

interface AuthChallengeResponse
{
    public function getChallengeName(): string;

    public function getChallengeId(): string;

    public function getUsername(): string;

    public function getChallengeResponses(): array;

    public function isSecretHashRequired(): bool;
}
