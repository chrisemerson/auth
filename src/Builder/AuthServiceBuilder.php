<?php declare(strict_types = 1);

use CEmerson\Auth\Auth;
use CEmerson\Auth\Users\AuthUserGateway;

class AuthServiceBuilder
{
    /** @var AuthUserGateway */
    private $authUserGateway;

    public function __construct()
    {
        $this->authUserGateway = null;
    }

    public function setUserGateway(AuthUserGateway $authUserGateway): self
    {
        $this->authUserGateway = $authUserGateway;

        return $this;
    }

    public function build(): Auth
    {
        return new Auth($this->authUserGateway);
    }
}
