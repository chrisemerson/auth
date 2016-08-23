<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Users;

use CEmerson\AceAuth\Exceptions\UserNotFound;

class CompositeUserGateway implements UserGateway
{
    /** @var UserGateway[] */
    private $userGateways = [];

    public function addUserGateway(UserGateway $userGateway)
    {
        $this->userGateways[] = $userGateway;
    }

    public function findUserByUsername(string $username): User
    {
        foreach ($this->userGateways as $userGateway) {
            try {
                return $userGateway->findUserByUsername($username);
            } catch (UserNotFound $e) {
            }
        }

        throw new UserNotFound();
    }
}
