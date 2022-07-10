<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use CEmerson\Auth\AuthResponses\AuthSucceededResponse;

class AwsCognitoAuthSucceededResponse implements AuthSucceededResponse
{
    private string $accessToken;
    private string $idToken;
    private ?string $refreshToken;
    private string $username;

    public const ACCESS_TOKEN_KEY_NAME = "accessToken";
    public const ID_TOKEN_KEY_NAME = "idToken";
    public const REFRESH_TOKEN_KEY_NAME = "refreshToken";
    public const USERNAME_KEY_NAME = "username";

    public function __construct(string $username, string $accessToken, string $idToken, ?string $refreshToken = null)
    {
        $this->username = $username;
        $this->accessToken = $accessToken;
        $this->idToken = $idToken;
        $this->refreshToken = $refreshToken;
    }

    public function getSessionInfo(): array
    {
        $sessionInfo = [
            self::ACCESS_TOKEN_KEY_NAME => $this->accessToken,
            self::ID_TOKEN_KEY_NAME => $this->idToken,
            self::USERNAME_KEY_NAME => $this->username
        ];

        if (!is_null($this->refreshToken)) {
            $sessionInfo[self::REFRESH_TOKEN_KEY_NAME] = $this->refreshToken;
        }

        return $sessionInfo;
    }

    public function getRememberedLoginInfo(): array
    {
        return [
            self::USERNAME_KEY_NAME => $this->username,
            self::REFRESH_TOKEN_KEY_NAME => $this->refreshToken
        ];
    }
}
