<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth;

use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\User;
use CEmerson\Auth\Users\UserGateway;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthSpec extends ObjectBehavior
{
    function let(UserGateway $userGateway, Session $session)
    {
        $this->beConstructedWith($userGateway, $session);
    }

    function it_checks_the_user_gateways_to_find_user(
        UserGateway $userGateway,
        User $user
    ) {
        $user->verifyPassword(Argument::type('string'))->willReturn(true);
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willReturn($user);

        $this->login('username', 'password');
    }

    function it_doesnt_log_in_when_user_isnt_found_in_user_gateway(
        UserGateway $userGateway
    ) {
        $userGateway->findUserByUsername("username")->willThrow(new UserNotFound());

        $this->login("username", "password")->shouldReturn(false);
    }

    function it_doesnt_log_in_when_the_password_is_incorrect_for_the_returned_user(
        UserGateway $userGateway,
        User $user
    ) {
        $user->verifyPassword('testPassword')->shouldBeCalled()->willReturn(false);
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willReturn($user);

        $this->login('username', 'testPassword')->shouldReturn(false);
    }

    function it_logs_in_when_the_password_is_correct_for_the_returned_user(
        UserGateway $userGateway,
        User $user
    ) {
        $user->verifyPassword('testPassword')->shouldBeCalled()->willReturn(true);
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willReturn($user);

        $this->login('username', 'testPassword')->shouldReturn(true);
    }

    function it_delegates_session_management_to_the_session_object_on_login(
        UserGateway $userGateway,
        User $user,
        Session $session
    ) {
        $user->verifyPassword('testPassword')->shouldBeCalled()->willReturn(true);
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willReturn($user);

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login('username', 'testPassword');
    }

    function it_doesnt_set_up_the_session_when_login_fails_due_to_user_not_found(
        UserGateway $userGateway,
        Session $session
    ) {
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willThrow(new UserNotFound());

        $session->onSuccessfulAuthentication()->shouldNotBeCalled();

        $this->login('username', 'testPassword');
    }

    function it_doesnt_set_up_the_session_when_login_fails_due_to_bad_password(
        UserGateway $userGateway,
        User $user,
        Session $session
    ) {
        $user->verifyPassword('testPassword')->shouldBeCalled()->willReturn(false);
        $userGateway->findUserByUsername('username')->shouldBeCalled()->willReturn($user);

        $session->onSuccessfulAuthentication()->shouldNotBeCalled();

        $this->login('username', 'testPassword');
    }

    function it_destroys_the_session_when_user_logs_out(Session $session)
    {
        $session->deleteAuthSessionInfo()->shouldBeCalled();

        $this->logout();
    }

    function it_delegates_queries_about_whether_the_user_is_logged_in_to_the_session(Session $session)
    {
        $session->userIsLoggedIn()->willReturn(false);
        $this->isLoggedIn()->shouldReturn(false);

        $session->userIsLoggedIn()->willReturn(true);
        $this->isLoggedIn()->shouldReturn(true);
    }

    function it_throws_an_exception_when_getting_the_current_user_if_the_user_is_not_logged_in(Session $session)
    {
        $session->userIsLoggedIn()->willReturn(false);

        $this->shouldThrow(new NoUserLoggedIn())->during('getCurrentUser');
    }

    function it_returns_the_currently_logged_in_user(Session $session, UserGateway $userGateway, User $user)
    {
        $session->userIsLoggedIn()->willReturn(true);
        $session->getLoggedInUsername()->willReturn('test_username');

        $userGateway->findUserByUsername('test_username')->shouldBeCalled()->willReturn($user);

        $this->getCurrentUser()->shouldReturn($user);
    }

    function it_delegates_queries_about_whether_the_user_has_authenticated_this_session_to_the_session_object(
        Session $session
    ) {
        $session->userHasAuthenticatedThisSession()->willReturn(false);
        $this->hasAuthenticatedThisSession()->shouldReturn(false);

        $session->userHasAuthenticatedThisSession()->willReturn(true);
        $this->hasAuthenticatedThisSession()->shouldReturn(true);
    }
}
