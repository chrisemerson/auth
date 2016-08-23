<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\PasswordHashingStrategies;

use Defuse\Crypto\Key;
use ParagonIE\PasswordLock\PasswordLock;

final class PasswordLockPasswordHashingStrategy implements PasswordHashingStrategy
{
    /** @var Key */
    private $encryptionKey;

    public function __construct(Key $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    public function hashPassword(string $password): string
    {
        return PasswordLock::hashAndEncrypt($password, $this->encryptionKey);
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return PasswordLock::decryptAndVerify($passwordToVerify, $passwordHash, $this->encryptionKey);
    }
}
