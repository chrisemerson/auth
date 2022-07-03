<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use StellaMaris\Clock\ClockInterface;

class AwsCognitoJwtTokenValidator
{
    private AwsCognitoConfiguration $awsCognitoConfiguration;
    private ClockInterface $clock;

    public function __construct(AwsCognitoConfiguration $awsCognitoConfiguration, ClockInterface $clock)
    {
        $this->awsCognitoConfiguration = $awsCognitoConfiguration;
    }

    public function validateToken(string $token): bool
    {
        $configuration = $this->buildConfiguration();

        try {
            $unencryptedToken = $configuration->parser()->parse($token);

            $configuration->validator()->assert(
                $unencryptedToken,
                ...$configuration->validationConstraints()
            );

            return true;
        } catch (RequiredConstraintsViolated $e) {
            return false;
        }
    }

    private function buildConfiguration(): Configuration
    {




        /**
To verify JWT claims

    1. Verify that the token is not expired.

    2. The aud claim in an ID token and the client_id claim in an access token should match the app client ID that was created in the Amazon Cognito user pool.

    3. The issuer (iss) claim should match your user pool. For example, a user pool created in the us-east-1 Region will have the following iss value:

    https://cognito-idp.us-east-1.amazonaws.com/<userpoolID>.

    4. Check the token_use claim.

        If you are only accepting the access token in your web API operations, its value must be access.
        If you are only using the ID token, its value must be id.
        If you are using both ID and access tokens, the token_use claim must be either id or access.

You can now trust the claims inside the token.
         */
        return Configuration::forAsymmetricSigner();
    }
}
