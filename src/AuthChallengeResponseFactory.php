<?php

namespace CEmerson\Auth;

use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;

interface AuthChallengeResponseFactory
{
    public function createAuthenticationResponse(
        string $authenticationChallengeName,
        string $authenticationChallengeDetails,
        string $authenticationChallengeResponse
    ): AuthChallengeResponse;
}
