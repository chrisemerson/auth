<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

interface RememberedLogin
{
    public function getUsername(): string;

    public function getToken(): string;
}
