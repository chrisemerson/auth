<?php

declare(strict_types=1);

namespace CEmerson\Auth\Users;

use CEmerson\Auth\Providers\Local\PasswordHashingStrategies\PasswordHashingStrategy;

interface WriteBackAuthUserGateway
{
    public function getPasswordHashingStrategy(): PasswordHashingStrategy;

    public function saveUser(AuthUser $oldUser, string $newPasswordHash);
}
