<?php declare(strict_types = 1);

namespace CEmerson\AceAuth;

use CEmerson\AceAuth\Exceptions\UserNotFound;
use CEmerson\AceAuth\Users\UserGateway;

class AceAuth
{
    /** @var UserGateway */
    private $userGateway;

    public function __construct(UserGateway $userGateway)
    {
        $this->userGateway = $userGateway;
    }

    public function login(string $username, string $password, bool $remember = false): bool
    {
        try {
            $user = $this->userGateway->findUserByUsername($username);
            return $user->verifyPassword($password);
        } catch (UserNotFound $e) {
        }

        return false;
    }
}
