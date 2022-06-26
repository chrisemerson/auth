<?php

declare(strict_types=1);

namespace CEmerson\Auth;

class AuthParameters
{
    private string $username;
    private ?string $password;
    private bool $rememberMe;
    private ?string $deviceKey;

    public function __construct(
        string $username,
        ?string $password = null,
        bool $rememberMe = false,
        ?string $deviceKey = null
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->rememberMe = $rememberMe;
        $this->deviceKey = $deviceKey;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRememberMe(): bool
    {
        return $this->rememberMe;
    }

    public function getDeviceKey(): ?string
    {
        return $this->deviceKey;
    }
}
