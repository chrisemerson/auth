<?php

declare(strict_types=1);

namespace CEmerson\Auth\User;

interface AuthUser
{
    public function getUsername(): string;
}
