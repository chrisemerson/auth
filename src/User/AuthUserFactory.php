<?php
declare(strict_types=1);

namespace CEmerson\Auth\User;

interface AuthUserFactory
{
    public function getAuthUser(DefaultAuthUser $defaultAuthUser): AuthUser;
}
