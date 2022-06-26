<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

interface AuthContext
{
    public function saveSessionInfo(array $sessionInfo): void;

    public function deleteSessionInfo(): void;

    public function saveRememberedLoginInfo(array $rememberedLoginInfo): void;

    public function deleteRememberedLoginInfo(): void;
}
