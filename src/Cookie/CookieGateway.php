<?php declare(strict_types = 1);

namespace CEmerson\Auth\Cookie;

interface CookieGateway
{
    public function write(string $key, string $value, int $secondsUntilExpiry = 30 * 24 * 60 * 60);

    public function exists(string $key): bool;

    public function read(string $key): string;

    public function delete(string $key);
}
