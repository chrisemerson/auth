<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use CEmerson\Auth\AuthResponses\AuthSucceededResponse;

class AwsCognitoAuthSucceededResponse implements AuthSucceededResponse
{
    private string $accessToken;
    private string $idToken;
    private string $refreshToken;

    public function __construct(string $accessToken, string $idToken, string $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->idToken = $idToken;
        $this->refreshToken = $refreshToken;
    }

    public function getSessionInfo(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'idToken' => $this->idToken,
            'refreshToken' => $this->refreshToken
        ];
    }

    public function getRememberedLoginInfo(): array
    {
        return [
            'refreshToken' => $this->refreshToken
        ];
    }
}
