<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Users\User;

interface Session
{
    public function onSuccessfulAuthentication(User $authenticatedUser);

    public function destroySession();
}
