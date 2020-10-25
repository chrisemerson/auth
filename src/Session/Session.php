<?php declare(strict_types = 1);

namespace CEmerson\Auth\Session;

use CEmerson\Auth\Users\AuthUser;

interface Session
{
    public function init();

    public function userIsLoggedIn(): bool;

    public function getLoggedInUsername(): string;

    public function userHasAuthenticatedThisSession(): bool;

    public function onSuccessfulAuthentication(AuthUser $authenticatedUser);

    public function deleteAuthSessionInfo();
}
