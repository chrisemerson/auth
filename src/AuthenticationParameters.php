<?php

namespace CEmerson\Auth;

class AuthenticationParameters
{
    private string $username;
    private ?string $password;
    private ?string $deviceKey;

    public function __construct(
        string $username,
        ?string $password = null,
        ?string $deviceKey = null
    ) {
        $this->username = $username;
        $this->password = $password;
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

    public function getDeviceKey(): ?string
    {
        return $this->deviceKey;
    }
}
