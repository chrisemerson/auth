<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Users;

use CEmerson\AceAuth\PasswordHashingStrategies\PasswordHashingStrategy;

abstract class User
{
    abstract public function getPasswordHashingStrategy(): PasswordHashingStrategy;
    abstract public function getUsername(): string;
    abstract public function getPasswordHash(): string;

    public function verifyPassword(string $password): bool
    {
        return $this
            ->getPasswordHashingStrategy()
            ->verifyPassword(
                $password,
                $this->getPasswordHash()
            );
    }
}