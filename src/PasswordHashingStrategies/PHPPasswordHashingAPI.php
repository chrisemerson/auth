<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\PasswordHashingStrategies;

final class PHPPasswordHashingAPI implements PasswordHashingStrategy
{
    /** @var int */
    private $algorithm = PASSWORD_DEFAULT;

    /** @var array */
    private $options = [];

    public function setAlgorithm(int $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function setCost(int $cost)
    {
        $this->options['cost'] = $cost;
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, $this->algorithm, $this->options);
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return password_verify($passwordToVerify, $passwordHash);
    }
}
