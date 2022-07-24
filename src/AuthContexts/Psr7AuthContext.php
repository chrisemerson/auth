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

    public function getSessionInfo(): array
    {
        return $_SESSION;
    }

    public function saveSessionInfo(array $sessionInfo): void
    {
        foreach ($sessionInfo as $name => $value) {
            $_SESSION[$name] = $value;
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
            $this->newRememberedLoginInfo[$name] = $value;
            $this->rememberedLoginInfoToExpire[$name] = false;
        }
    }

    public function deleteRememberedLoginInfo(): void
    {
        foreach ($this->getRememberedLoginInfo() as $name => $_) {
            $this->rememberedLoginInfoToExpire[$name] = true;
            $this->newRememberedLoginInfo = [];
        }
    }

    public function populateContextFromPsr7Request(ServerRequestInterface $request)
    {
        foreach (Cookies::fromRequest($request)->getAll() as $cookie) {
            $this->rememberedLoginInfo[$cookie->getName()] = $cookie->getValue();
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
