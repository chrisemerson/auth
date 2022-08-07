<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use CEmerson\Auth\AuthChallengeResponseFactory;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\Exceptions\InvalidChallenge;

class AwsCognitoAuthChallengeResponseFactory implements AuthChallengeResponseFactory
{
    public function createAuthenticationResponse(
        string $authenticationChallengeName,
        string $authenticationChallengeDetails,
        string $authenticationChallengeResponse
    ): AuthChallengeResponse {
        $className =
            "CEmerson\\Auth\\Providers\\AwsCognito\\AuthChallenges\\"
            . $authenticationChallengeName
            . "\\"
            . $authenticationChallengeName
            . "Challenge";

        $interfacesImplemented = class_implements($className);

        if ($interfacesImplemented !== false && in_array(AuthChallenge::class, $interfacesImplemented)) {
            return
                ($className::fromJson($authenticationChallengeDetails))
                    ->createChallengeResponse($authenticationChallengeResponse);
        }

        throw new InvalidChallenge("Challenge " . $authenticationChallengeName . " is not a valid challenge");
    }
}
