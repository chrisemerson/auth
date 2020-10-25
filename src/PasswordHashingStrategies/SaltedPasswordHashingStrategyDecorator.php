<?php declare(strict_types = 1);

namespace CEmerson\Auth\PasswordHashingStrategies;

final class SaltedPasswordHashingStrategyDecorator implements PasswordHashingStrategy
{
    /** @var PasswordHashingStrategy */
    private $passwordHashingStrategy;

    /** @var string */
    private $salt;

    final public function __construct(PasswordHashingStrategy $passwordHashingStrategy)
    {
        $this->passwordHashingStrategy = $passwordHashingStrategy;
    }

    final public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    final public function hashPassword(string $password): string
    {
        return $this->passwordHashingStrategy->hashPassword(
            $this->getSaltedPassword($password, $this->salt)
        );
    }

    final public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return $this->passwordHashingStrategy->verifyPassword(
            $this->getSaltedPassword($passwordToVerify, $this->salt),
            $passwordHash
        );
    }

    protected function getSaltedPassword(string $password, string $salt): string
    {
        return $password . $salt;
    }
}
