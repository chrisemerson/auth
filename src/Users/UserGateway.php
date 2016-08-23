<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Users;

interface UserGateway
{
    public function findUserByUsername(string $username): User;
}
