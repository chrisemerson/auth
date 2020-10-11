<?php declare(strict_types = 1);

namespace CEmerson\Auth\PasswordHashingStrategies\PHPPasswordAPI;

final class PHPPasswordAPIWrapperImplementation implements PHPPasswordAPIWrapper
{
    public function hash(string $password, string $algo = PASSWORD_DEFAULT, array $options = []): string
    {
        return password_hash($password, $algo, $options);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
