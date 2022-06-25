<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\Local\PasswordHashingStrategies;

use CEmerson\Auth\Exceptions\PlainTextPasswordStorageNotSupported;

final class PlainTextPasswordHashingStrategy implements PasswordHashingStrategy
{
    public function hashPassword(string $password): string
    {
        throw new PlainTextPasswordStorageNotSupported();
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return strcmp($passwordToVerify, $passwordHash) == 0;
    }
}
