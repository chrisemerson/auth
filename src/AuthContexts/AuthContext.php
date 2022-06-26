<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

interface AuthContext
{
    public function getSessionInfo(): array;

    public function saveSessionInfo(array $sessionInfo): void;

    public function deleteSessionInfo(): void;

    public function getRememberedLoginInfo(): array;

    public function saveRememberedLoginInfo(array $rememberedLoginInfo): void;

    public function deleteRememberedLoginInfo(): void;
}
