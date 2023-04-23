<?php

declare(strict_types=1);

namespace CEmerson\Auth\User;

class DefaultAuthUser implements AuthUser
{
    private string $username;
    private array $userInfo;

    public function __construct(string $username, array $userInfo)
    {
        $this->username = $username;
        $this->userInfo = $userInfo;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function has(string $field): bool
    {
        return isset($this->userInfo[$field]);
    }

    public function get(string $field, ?string $default = null): ?string
    {
        if ($this->has($field)) {
            return $this->userInfo[$field];
        }

        return (string) $default;
    }
}
