<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use CEmerson\Auth\AuthResponses\AuthSucceededResponse;

class AwsCognitoAuthSucceededResponse implements AuthSucceededResponse
{
    private string $accessToken;
    private string $idToken;
    private string $refreshToken;

    public const ACCESS_TOKEN_KEY_NAME = "accessToken";
    public const ID_TOKEN_KEY_NAME = "idToken";
    public const REFRESH_TOKEN_KEY_NAME = "refreshToken";

    public function __construct(string $accessToken, string $idToken, string $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->idToken = $idToken;
        $this->refreshToken = $refreshToken;
    }

    public function getSessionInfo(): array
    {
        return [
            self::ACCESS_TOKEN_KEY_NAME => $this->accessToken,
            self::ID_TOKEN_KEY_NAME => $this->idToken,
            self::REFRESH_TOKEN_KEY_NAME => $this->refreshToken
        ];
    }

    public function getRememberedLoginInfo(): array
    {
        return [
            self::REFRESH_TOKEN_KEY_NAME => $this->refreshToken
        ];
    }
}
