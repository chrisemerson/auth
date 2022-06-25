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

    public function __construct(
        CognitoIdentityProviderClient $awsCognitoClient,
        string $userPoolId,
        string $clientId,
        string $clientSecret
    ) {
        $this->awsCognitoClient = $awsCognitoClient;
        $this->userPoolId = $userPoolId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getAwsCognitoClient(): CognitoIdentityProviderClient
    {
        return $this->awsCognitoClient;
    }

    public function getUserPoolId(): string
    {
        return $this->userPoolId;
    }

    public function getClientId(): string
    {
        return $this->clientId;
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
