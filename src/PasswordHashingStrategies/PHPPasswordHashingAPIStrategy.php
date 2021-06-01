<?php declare(strict_types = 1);

namespace CEmerson\Auth\PasswordHashingStrategies;

use CEmerson\Auth\PasswordHashingStrategies\PHPPasswordAPI\PHPPasswordAPIWrapper;

final class PHPPasswordHashingAPIStrategy implements PasswordHashingStrategy
{
    /** @var PHPPasswordAPIWrapper */
    private $PHPPasswordAPIWrapper;

    /** @var string */
    private $algorithm = PASSWORD_DEFAULT;

    /** @var array */
    private $options = [];

    public function __construct(PHPPasswordAPIWrapper $PHPPasswordAPIWrapper)
    {
        $this->PHPPasswordAPIWrapper = $PHPPasswordAPIWrapper;
    }

    public function setAlgorithm(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function setCost(int $cost)
    {
        $this->options['cost'] = $cost;
    }

    public function hashPassword(string $password): string
    {
        return $this->PHPPasswordAPIWrapper->hash($password, $this->algorithm, $this->options);
    }

    public function verifyPassword(string $passwordToVerify, string $passwordHash): bool
    {
        return $this->PHPPasswordAPIWrapper->verify($passwordToVerify, $passwordHash);
    }
}
