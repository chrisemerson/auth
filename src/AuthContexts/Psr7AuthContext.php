<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Psr7AuthContext implements AuthContext
{
    private array $sessionInfo = [];

    private array $rememberedLoginInfo = [];

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
//        session_destroy();
    }

    public function getRememberedLoginInfo(): array
    {
        return $this->rememberedLoginInfo;
    }

    public function saveRememberedLoginInfo(array $rememberedLoginInfo): void
    {
        $this->rememberedLoginInfo = $rememberedLoginInfo;
    }

    public function deleteRememberedLoginInfo(): void
    {
        $this->rememberedLoginInfo = [];
    }

    public function populateContextFromPsr7Request(ServerRequestInterface $request)
    {
    }

    public function addContextValuesToResponse(ResponseInterface $response)
    {
    }
}
