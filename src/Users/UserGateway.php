<?php declare(strict_types = 1);

namespace CEmerson\Auth\Users;

interface UserGateway
{
    public function findUserByUsername(string $username): AuthUser;
}
