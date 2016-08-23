<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

final class AceAuthSession implements Session
{
    /** @var SessionGateway */
    private $sessionGateway;

    public function __construct(SessionGateway $sessionGateway)
    {
        $this->sessionGateway = $sessionGateway;
    }
}
