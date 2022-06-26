<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

class JwtTokenValidator
{
    private AwsCognitoConfiguration $awsCognitoConfiguration;

    public function __construct(AwsCognitoConfiguration $awsCognitoConfiguration)
    {
        $this->awsCognitoConfiguration = $awsCognitoConfiguration;
    }

    public function validateToken(string $token): bool
    {
        //TODO: Validate token here

        return false;
    }
}
