<?php declare(strict_types = 1);

namespace CEmerson\Auth\Users;

use CEmerson\Auth\PasswordHashingStrategies\PasswordHashingStrategy;

interface AuthUser
{
    public function getPasswordHashingStrategy(): PasswordHashingStrategy;

    public function getUsername(): string;

    public function getPasswordHash(): string;
}
