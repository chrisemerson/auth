<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\Users;

use PhpSpec\ObjectBehavior;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\Users\User;
use CEmerson\Auth\Users\UserGateway;

class CompositeUserGatewaySpec extends ObjectBehavior
{
    function it_checks_the_user_gateways_to_find_user(UserGateway $userGateway) {
        $userGateway->findUserByUsername('username')->shouldBeCalled();

        $this->addUserGateway($userGateway);

        $this->findUserByUsername('username');
    }

    function it_checks_a_second_user_gateway_if_the_user_isnt_found_in_the_first(
        UserGateway $userGateway,
        UserGateway $userGateway2
    ) {
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willThrow(new UserNotFound());
        $userGateway2->findUserByUsername('username')->shouldBeCalled();

        $this->addUserGateway($userGateway);
        $this->addUserGateway($userGateway2);

        $this->findUserByUsername("username");
    }

    function it_doesnt_check_the_second_user_gateway_if_the_user_is_found_by_the_first(
        UserGateway $userGateway,
        UserGateway $userGateway2,
        User $user
    ) {
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willReturn($user);
        $userGateway2->findUserByUsername('username')->shouldNotBeCalled();

        $this->addUserGateway($userGateway);
        $this->addUserGateway($userGateway2);

        $this->findUserByUsername('username');
    }

    function it_throws_an_error_when_users_arent_found_in_any_user_gateways(
        UserGateway $userGateway,
        UserGateway $userGateway2
    ) {
        $userGateway->findUserByUsername('username')->willThrow(new UserNotFound());
        $userGateway2->findUserByUsername('username')->willThrow(new UserNotFound());

        $this->addUserGateway($userGateway);
        $this->addUserGateway($userGateway2);

        $this->shouldThrow(new UserNotFound())->during('findUserByUsername', ['username']);
    }
}
