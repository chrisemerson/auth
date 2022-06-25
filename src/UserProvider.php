<?php

declare(strict_types=1);

namespace CEmerson\Auth;

interface UserProvider
{
    public function getLoggedInUserIdentifier(): string;

    public function getLoggedInUsername(): string;
}
