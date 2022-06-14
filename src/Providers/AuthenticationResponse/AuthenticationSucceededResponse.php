<?php

namespace CEmerson\Auth\Providers\AuthenticationResponse;

use CEmerson\Auth\TokenProvider;

class AuthenticationSucceededResponse implements AuthenticationResponse, TokenProvider
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

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getIdToken(): string
    {
        return $this->idToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}