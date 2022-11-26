<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts\Session;

use CEmerson\Auth\User\AuthUser;

interface Session
{
    public function init();

    public function userIsLoggedIn(): bool;

    public function userHasAuthenticatedThisSession(): bool;

    public function setCurrentlyLoggedInUser(AuthUser $currentUser);

    public function onSuccessfulAuthentication(AuthUser $authenticatedUser);

    public function deleteAuthSessionInfo();
}
