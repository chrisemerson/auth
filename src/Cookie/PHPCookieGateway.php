<?php declare(strict_types = 1);

namespace CEmerson\Auth\Cookie;

use CEmerson\Auth\Exceptions\CookieError;
use CEmerson\Clock\Clock;

final class PHPCookieGateway implements CookieGateway
{
    /** @var string */
    private $cookieDomain;

    /** @var bool */
    private $secure;

    /** @var Clock */
    private $clock;

    public function __construct(string $cookieDomain, bool $secure, Clock $clock)
    {
        $this->cookieDomain = $cookieDomain;
        $this->secure = $secure;
        $this->clock = $clock;
    }

    public function write(string $key, string $value, int $secondsUntilExpiry = 30 * 24 * 60 * 60)
    {
        if (!setcookie($key, $value, $this->getCurrentTimestamp() + $secondsUntilExpiry, null, $this->cookieDomain, $this->secure)) {
            throw new CookieError("Unable to set cookie");
        }
    }

    public function exists(string $key): bool
    {
        return isset($_COOKIE[$key]);
    }

    public function read(string $key): string
    {
        return $_COOKIE[$key];
    }

    public function delete(string $key)
    {
        if (!setcookie($key, '', $this->getCurrentTimestamp() - 3600, null, $this->cookieDomain, $this->secure)) {
            throw new CookieError("Unable to delete cookie");
        }
    }

    private function getCurrentTimestamp(): int
    {
        return (int) $this->clock->getDateTime()->format('u');
    }
}
