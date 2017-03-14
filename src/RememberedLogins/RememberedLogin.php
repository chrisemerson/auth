<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

use DateTimeInterface;

interface RememberedLogin
{
    public function getUsername(): string;

    public function setUsername(string $username);

    public function getSelector(): string;

    public function setSelector(string $selector);

    public function getToken(): string;

    public function setToken(string $token);

    public function getExpiryDateTime(): DateTimeInterface;

    public function setExpiryDateTime(DateTimeInterface $expiry);
}
