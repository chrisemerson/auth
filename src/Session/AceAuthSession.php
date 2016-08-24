<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Users\User;

final class AceAuthSession implements Session
{
    /** @var SessionGateway */
    private $sessionGateway;

    public function __construct(SessionGateway $sessionGateway)
    {
        $this->sessionGateway = $sessionGateway;
    }

    public function onSuccessfulAuthentication(User $authenticatedUser)
    {
    }

    public function destroySession()
    {
    }
}
