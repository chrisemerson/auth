<?php

namespace CEmerson\Auth\AuthenticationResponse\AuthenticationChallenge;

interface AuthenticationChallengeResponse
{
    public function getChallengeName(): string;

    public function getChallengeId(): string;

    public function getUsername(): string;

    public function getChallengeResponses(): array;

    public function isSecretHashRequired(): bool;
}
