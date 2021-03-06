<?php declare(strict_types = 1);

namespace CEmerson\Auth\PasswordHashingStrategies;

interface PasswordHashingStrategy
{
    public function hashPassword(string $password): string;

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool;
}
