<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Psr7AuthContext implements AuthContext
{
    private array $sessionInfo = [];

    private array $rememberedLoginInfo = [];

    public function saveSessionInfo(array $sessionInfo): void
    {
        $this->sessionInfo = $sessionInfo;
    }

    public function deleteSessionInfo(): void
    {
        $this->sessionInfo = [];
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
