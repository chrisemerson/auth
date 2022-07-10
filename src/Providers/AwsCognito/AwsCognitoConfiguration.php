<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

class AwsCognitoConfiguration
{
    private CognitoIdentityProviderClient $awsCognitoClient;
    private string $userPoolId;
    private string $clientId;
    private string $clientSecret;
    private string $jsonWebKeySet;

    public function __construct(
        CognitoIdentityProviderClient $awsCognitoClient,
        string $userPoolId,
        string $clientId,
        string $clientSecret,
        string $jsonWebKeySet
    ) {
        $this->awsCognitoClient = $awsCognitoClient;
        $this->userPoolId = $userPoolId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->jsonWebKeySet = $jsonWebKeySet;
    }

    public function getAwsCognitoClient(): CognitoIdentityProviderClient
    {
        return $this->awsCognitoClient;
    }

    public function getUserPoolId(): string
    {
        return $this->userPoolId;
    }

    public function getUserPoolUri(): string
    {
        return 'https://cognito-idp.' . $this->awsCognitoClient->getRegion() . '.amazonaws.com/' . $this->userPoolId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getJsonWebKeySet(): string
    {
        return $this->jsonWebKeySet;
    }

    public function hash(string $string)
    {
        return base64_encode(
            hash_hmac(
                'sha256',
                $string,
                $this->clientSecret,
                true
            )
        );
    }
}
