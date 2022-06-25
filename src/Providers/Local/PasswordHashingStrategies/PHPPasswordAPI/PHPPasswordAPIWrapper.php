<?php  declare(strict_types = 1);

namespace CEmerson\Auth\Providers\Local\PasswordHashingStrategies\PHPPasswordAPI;

interface PHPPasswordAPIWrapper
{
    public function hash(string $password, string $algo = PASSWORD_DEFAULT, array $options = []) : string;

    public function verify(string $password, string $hash) : bool;
}
