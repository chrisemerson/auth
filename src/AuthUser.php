<?php

declare(strict_types=1);

namespace CEmerson\Auth;

class AuthUser
{
    private string $id;
    private array $userInfo;

    public function __construct(string $id, array $userInfo)
    {
        $this->id = $id;
        $this->userInfo = $userInfo;
    }

    public function getId(): string
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

        return $default;
    }
}
