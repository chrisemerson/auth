<?php declare(strict_types = 1);

namespace CEmerson\Auth\Users;

use CEmerson\Auth\PasswordHashingStrategies\PasswordHashingStrategy;

interface WriteBackAuthUserGateway
{
    public function getPasswordHashingStrategy(): PasswordHashingStrategy;

    public function saveUser(AuthUser $user, string $newPasswordHash);
}
