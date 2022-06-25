<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts\Cookie;

use DateTimeInterface;

interface CookieGateway
{
    public function write(string $key, string $value, DateTimeInterface $expiryDateTime);

    public function exists(string $key): bool;

    public function read(string $key): string;

    public function delete(string $key);
}
