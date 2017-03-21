<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

use DateTimeInterface;

interface RememberedLogin
{
    public function getUsername(): string;

    public function getSelector(): string;

    public function getToken(): string;

    public function getExpiryDateTime(): DateTimeInterface;
}
