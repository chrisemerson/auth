<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

use Dflydev\FigCookies\Cookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Psr7AuthContext implements AuthContext
{
    private array $sessionInfo = [];

    private array $rememberedLoginInfo = [];
    private array $newRememberedLoginInfo = [];
    private array $rememberedLoginInfoToExpire = [];

    private string $prefix;

    public function __construct(string $prefix = 'ceauth_')
    {
        $this->prefix = $prefix;
    }

    public function getSessionInfo(): array
    {
        $sessionInfo = [];

        foreach ($_SESSION as $name => $value) {
            if (substr($name, 0, strlen($this->prefix)) === $this->prefix) {
                $sessionInfo[substr($name, strlen($this->prefix))] = $value;
            }
        }

        return $sessionInfo;
    }

    public function saveSessionInfo(array $sessionInfo): void
    {
        foreach ($sessionInfo as $name => $value) {
            $_SESSION[$this->prefix . $name] = $value;
        }
    }

    public function deleteSessionInfo(): void
    {
        session_destroy();
    }

    public function getRememberedLoginInfo(): array
    {
        return $this->rememberedLoginInfo;
    }

    public function saveRememberedLoginInfo(array $rememberedLoginInfo): void
    {
        foreach ($rememberedLoginInfo as $name => $value) {
            $this->newRememberedLoginInfo[$this->prefix . $name] = $value;
            $this->rememberedLoginInfoToExpire[$this->prefix . $name] = false;
        }
    }

    public function deleteRememberedLoginInfo(): void
    {
        foreach ($this->getRememberedLoginInfo() as $name => $_) {
            $this->rememberedLoginInfoToExpire[$this->prefix . $name] = true;
            $this->newRememberedLoginInfo = [];
        }
    }

    public function populateContextFromPsr7Request(ServerRequestInterface $request)
    {
        foreach (Cookies::fromRequest($request)->getAll() as $cookie) {
            if (substr($cookie->getName(), 0, strlen($this->prefix)) === $this->prefix) {
                $this->rememberedLoginInfo[substr($cookie->getName(), strlen($this->prefix))] = $cookie->getValue();
            }
        }
    }

    public function addContextValuesToResponse(ResponseInterface $response): ResponseInterface
    {
        foreach (array_keys(array_filter($this->rememberedLoginInfoToExpire)) as $cookieToExpire) {
            $response = FigResponseCookies::expire(
                $response,
                $cookieToExpire
            );
        }

        foreach ($this->newRememberedLoginInfo as $name => $value) {
            $response = FigResponseCookies::set(
                $response,
                SetCookie::create($name)
                    ->withValue($value)
            );
        }

        return $response;
    }
}
