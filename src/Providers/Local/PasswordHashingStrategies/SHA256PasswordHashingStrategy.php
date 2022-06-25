<?php declare(strict_types = 1);

namespace CEmerson\Auth\Providers\Local\PasswordHashingStrategies;

final class SHA256PasswordHashingStrategy implements PasswordHashingStrategy
{
    public function hashPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return strcasecmp($this->hashPassword($passwordToVerify), $passwordHash) == 0;
    }
}
