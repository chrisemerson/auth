<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth;

use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\PasswordHashingStrategies\PasswordHashingStrategy;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
use CEmerson\Auth\Users\WriteBackAuthUserGateway;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthSpec extends ObjectBehavior
{
    const TEST_USERNAME = 'test_username';
    const TEST_PASSWORD_HASH = 'test_password_hash';
    const TEST_PASSWORD_NEW_HASH = 'test_password_new_hash';
    const TEST_PASSWORD = 'test_password';
    const TEST_WRONG_PASSWORD = 'test_wrong_password';

    function let(
        AuthUserGateway $userGateway,
        Session $session,
        AuthUser $user,
        PasswordHashingStrategy $passwordHashingStrategy
    ) {
        $user->getPasswordHashingStrategy()->willReturn($passwordHashingStrategy);
        $user->getPasswordHash()->willReturn(self::TEST_PASSWORD_HASH);

        $passwordHashingStrategy->verifyPassword(Argument::type('string'), self::TEST_PASSWORD_HASH)->willReturn(false);

        $userGateway->findUserByUsername(self::TEST_USERNAME)->willReturn($user);

        $this->beConstructedWith($userGateway, $session);
    }

    function it_checks_the_user_gateways_to_find_user(
        AuthUserGateway $userGateway,
        AuthUser $user
    ) {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->shouldBeCalled()->willReturn($user);

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_log_in_when_user_isnt_found_in_user_gateway(
        AuthUserGateway $userGateway
    ) {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->willThrow(new UserNotFound());

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD)->shouldReturn(false);
    }

    function it_doesnt_log_in_when_the_password_is_incorrect_for_the_returned_user(
        PasswordHashingStrategy $passwordHashingStrategy
    ) {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_WRONG_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->login(self::TEST_USERNAME, self::TEST_WRONG_PASSWORD)->shouldReturn(false);
    }

    function it_logs_in_when_the_password_is_correct_for_the_returned_user(
        PasswordHashingStrategy $passwordHashingStrategy
    ) {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD)->shouldReturn(true);
    }

    function it_delegates_session_management_to_the_session_object_on_login(
        PasswordHashingStrategy $passwordHashingStrategy,
        AuthUser $user,
        Session $session
    ) {
        $passwordHashingStrategy->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)->willReturn(true);

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_set_up_the_session_when_login_fails_due_to_user_not_found(
        AuthUserGateway $userGateway,
        Session $session
    ) {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->willThrow(new UserNotFound());

        $session->onSuccessfulAuthentication(Argument::any())->shouldNotBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_set_up_the_session_when_login_fails_due_to_bad_password(
        AuthUserGateway $userGateway,
        AuthUser $user,
        Session $session
    ) {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->willReturn($user);

        $session->onSuccessfulAuthentication()->shouldNotBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
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

    function it_returns_the_currently_logged_in_user(Session $session, AuthUserGateway $userGateway, AuthUser $user)
    {
        $session->userIsLoggedIn()->willReturn(true);
        $session->getLoggedInUsername()->willReturn(self::TEST_USERNAME);

        $userGateway->findUserByUsername(self::TEST_USERNAME)->shouldBeCalled()->willReturn($user);

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

    function it_takes_a_writeback_gateway_to_write_successful_logins_back_to(
        PasswordHashingStrategy $passwordHashingStrategy,
        WriteBackAuthUserGateway $writeBackAuthUserGateway,
        AuthUser $user
    ) {
        $passwordHashingStrategy->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)->willReturn(true);
        $passwordHashingStrategy->hashPassword(self::TEST_PASSWORD)->willReturn(self::TEST_PASSWORD_NEW_HASH);

        $writeBackAuthUserGateway->getPasswordHashingStrategy()->willReturn($passwordHashingStrategy);
        $writeBackAuthUserGateway->saveUser($user, self::TEST_PASSWORD_NEW_HASH)->shouldBeCalled();

        $this->setWriteBackAuthUserGateway($writeBackAuthUserGateway);

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_call_the_writeback_gateway_if_login_fails(
        WriteBackAuthUserGateway $writeBackAuthUserGateway,
        AuthUser $user
    ) {
        $writeBackAuthUserGateway->saveUser($user, self::TEST_PASSWORD_NEW_HASH)->shouldNotBeCalled();

        $this->setWriteBackAuthUserGateway($writeBackAuthUserGateway);

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }
}
