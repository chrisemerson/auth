<?php

declare(strict_types=1);

namespace CEmerson\Auth\User;

class DefaultAuthUser implements AuthUser
{
    private string $id;
    private array $userInfo;

    public function __construct(string $id, array $userInfo)
    {
        $this->id = $id;
        $this->userInfo = $userInfo;
    }

    public function getUserId(): string
    {
        return $this->id;
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
