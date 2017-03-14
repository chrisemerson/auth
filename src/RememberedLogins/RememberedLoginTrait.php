<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

use DateTimeInterface;

trait RememberedLoginTrait
{
    /** @var string */
    private $username;

    /** @var string */
    private $selector;

    /** @var string */
    private $token;

    /** @var DateTimeInterface */
    private $expiry;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function setSelector(string $selector)
    {
        $this->selector = $selector;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function getExpiryDateTime(): DateTimeInterface
    {
        return $this->expiry;
    }

    public function setExpiryDateTime(DateTimeInterface $expiry)
    {
        $this->expiry = $expiry;
    }
}
