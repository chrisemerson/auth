<?php declare(strict_types = 1);

namespace CEmerson\AceAuth;

use CEmerson\AceAuth\Exceptions\UserNotFound;
use CEmerson\AceAuth\Session\Session;
use CEmerson\AceAuth\Users\UserGateway;

final class AceAuth
{
    /** @var UserGateway */
    private $userGateway;

    /** @var Session */
    private $session;

    public function __construct(UserGateway $userGateway, Session $session)
    {
        $this->userGateway = $userGateway;
        $this->session = $session;
    }

    public function login(string $username, string $password, bool $remember = false): bool
    {
        try {
            $user = $this->userGateway->findUserByUsername($username);

            if ($user->verifyPassword($password)) {

                return true;
            }
        } catch (UserNotFound $e) {
        }

        return false;
    }
}
