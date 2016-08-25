<?php declare(strict_types = 1);

namespace CEmerson\Auth\Users;

use CEmerson\Auth\PasswordHashingStrategies\PasswordHashingStrategy;

final class TestUser extends User
{
    private $passwordHashingStrategy;

    public function __construct(PasswordHashingStrategy $passwordHashingStrategy)
    {
        $this->passwordHashingStrategy = $passwordHashingStrategy;
    }

    public function getPasswordHashingStrategy(): PasswordHashingStrategy
    {
        return $this->passwordHashingStrategy;
    }

    public function getUsername(): string
    {
        return 'test_username';
    }

    public function getPasswordHash(): string
    {
        return 'test_password_hash';
    }
}
