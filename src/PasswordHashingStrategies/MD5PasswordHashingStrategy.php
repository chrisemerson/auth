<?php declare(strict_types = 1);

namespace CEmerson\Auth\PasswordHashingStrategies;

final class MD5PasswordHashingStrategy implements PasswordHashingStrategy
{
    public function hashPassword(string $password): string
    {
        return md5($password);
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return strcasecmp($this->hashPassword($passwordToVerify), $passwordHash) == 0;
    }
}
