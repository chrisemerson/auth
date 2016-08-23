<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\PasswordHashingStrategies;

class SaltedPasswordHashingStrategyDecorator implements PasswordHashingStrategy
{
    /** @var PasswordHashingStrategy */
    private $passwordHashingStrategy;

    /** @var string */
    private $salt;

    public function __construct(PasswordHashingStrategy $passwordHashingStrategy)
    {
        $this->passwordHashingStrategy = $passwordHashingStrategy;
    }

    public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    public function hashPassword(string $password): string
    {
        return $this->passwordHashingStrategy->hashPassword(
            $this->getSaltedPassword($password)
        );
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return $this->passwordHashingStrategy->verifyPassword(
            $this->getSaltedPassword($passwordToVerify),
            $passwordHash
        );
    }

    private function getSaltedPassword(string $password): string
    {
        return $password . $this->salt;
    }
}
