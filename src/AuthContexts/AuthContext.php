<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

interface AuthContext
{
    public function deleteSessionInfo();

    public function deleteRememberedLogin();
}
