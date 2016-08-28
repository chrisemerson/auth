<?php declare(strict_types = 1);

namespace CEmerson\Auth\Users;

interface AuthUserGateway
{
    public function findUserByUsername(string $username): AuthUser;
}
