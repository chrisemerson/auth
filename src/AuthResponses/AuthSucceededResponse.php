<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthResponses;

use CEmerson\Auth\AuthResponse;

interface AuthSucceededResponse extends AuthResponse
{
    public function getSessionInfo(): array;

    public function getRememberedLoginInfo(): array;
}
