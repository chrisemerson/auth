<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

interface RememberedLoginGateway
{
    public function findRememberedLoginBySelector(string $selector): RememberedLogin;

    public function saveRememberedLogin(RememberedLogin $rememberedLogin);

    public function deleteRememberedLoginBySelector(string $selector);

    public function deleteAllRememberedLoginsForUser(string $username);

    public function cleanupExpiredRememberedLogins();
}
