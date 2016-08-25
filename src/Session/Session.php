<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Users\User;

interface Session
{
    public function init();

    public function userIsLoggedIn(): bool;

    public function getLoggedInUsername(): string;

    public function userHasAuthenticatedThisSession(): bool;

    public function onSuccessfulAuthentication(User $authenticatedUser);

    public function deleteAceAuthSessionInfo();
}
