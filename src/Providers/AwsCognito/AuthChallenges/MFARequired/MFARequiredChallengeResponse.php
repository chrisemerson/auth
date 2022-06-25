<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\AuthChallenges\MFARequired;

use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;

class MFARequiredChallengeResponse implements AuthChallengeResponse
{
    public function getChallengeName(): string
    {
        // TODO: Implement getChallengeName() method.
    }

    public function getChallengeId(): string
    {
        // TODO: Implement getChallengeId() method.
    }

    public function getUsername(): string
    {
        // TODO: Implement getUsername() method.
    }

    public function getChallengeParameters(): array
    {
        // TODO: Implement getChallengeResponses() method.
    }

    public function isSecretHashRequired(): bool
    {
        // TODO: Implement isSecretHashRequired() method.
    }
}
