<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

use DateTimeInterface;

interface RememberedLoginFactory
{
    public function createRememberedLogin(
        string $username,
        string $selector,
        string $token,
        DateTimeInterface $expiryDateTime
    ): RememberedLogin;
}
