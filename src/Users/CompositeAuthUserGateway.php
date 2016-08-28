<?php declare(strict_types = 1);

namespace CEmerson\Auth\Users;

use CEmerson\Auth\Exceptions\UserNotFound;

final class CompositeAuthUserGateway implements AuthUserGateway
{
    /** @var AuthUserGateway[] */
    private $userGateways = [];

    public function addUserGateway(AuthUserGateway $userGateway)
    {
        $this->userGateways[] = $userGateway;
    }

    public function findUserByUsername(string $username): AuthUser
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