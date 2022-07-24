<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

class NativePhpAuthContext implements AuthContext
{

    public function getSessionInfo(): array
    {

    }

    public function saveSessionInfo(array $sessionInfo): void
    {
        // TODO: Implement saveSessionInfo() method.
    }

    public function deleteSessionInfo(): void
    {
        // TODO: Implement deleteSessionInfo() method.
    }

    public function getRememberedLoginInfo(): array
    {

    }

    public function saveRememberedLoginInfo(array $rememberedLoginInfo): void
    {
        // TODO: Implement saveRememberedLoginInfo() method.
    }

    public function deleteRememberedLoginInfo(): void
    {
        // TODO: Implement deleteRememberedLoginInfo() method.
    }
}
