<?php  declare(strict_types = 1);

namespace CEmerson\Auth\PasswordHashingStrategies\PHPPasswordAPI;

interface PHPPasswordAPIWrapper
{
    public function hash(string $password, $algo = PASSWORD_DEFAULT, array $options = []) : string;

    public function verify(string $password, string $hash) : bool;
}
