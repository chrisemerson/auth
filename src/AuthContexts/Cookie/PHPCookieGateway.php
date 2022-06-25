<?php declare(strict_types = 1);

namespace CEmerson\Auth\AuthContexts\Cookie;

use CEmerson\Auth\Exceptions\CookieError;
use DateTimeInterface;

final class PHPCookieGateway implements CookieGateway
{
    /** @var string */
    private $cookieDomain;

    /** @var bool */
    private $secure;

    public function __construct(string $cookieDomain, bool $secure)
    {
        $this->cookieDomain = $cookieDomain;
        $this->secure = $secure;
    }

    public function write(string $key, string $value, DateTimeInterface $expiryDateTime)
    {
        if (!setcookie(
            $key,
            $value,
            $expiryDateTime->getTimestamp(),
            '/',
            $this->cookieDomain,
            $this->secure
        )) {
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
        if (!setcookie($key, '', 0, '/', $this->cookieDomain, $this->secure)) {
            throw new CookieError("Unable to delete cookie");
        }
    }
}
