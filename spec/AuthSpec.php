<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth;

use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserAlreadyLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\PasswordHashingStrategies\PasswordHashingStrategy;
use CEmerson\Auth\RememberedLogins\RememberedLoginService;
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
        RememberedLoginService $rememberedLoginService,
        AuthUser $user,
        PasswordHashingStrategy $passwordHashingStrategy
    )
    {
        $user->getPasswordHashingStrategy()->willReturn($passwordHashingStrategy);
        $user->getPasswordHash()->willReturn(self::TEST_PASSWORD_HASH);

        $passwordHashingStrategy->verifyPassword(Argument::type('string'), self::TEST_PASSWORD_HASH)->willReturn(false);

        $userGateway->findUserByUsername(self::TEST_USERNAME)->willReturn($user);

        $session->userIsLoggedIn()->willReturn(false);

        $this->beConstructedWith($userGateway, $session, $rememberedLoginService);
    }

    function it_checks_the_user_gateways_to_find_user(
        AuthUserGateway $userGateway,
        AuthUser $user
    )
    {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->shouldBeCalled()->willReturn($user);

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_log_in_when_user_isnt_found_in_user_gateway(
        AuthUserGateway $userGateway
    )
    {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->willThrow(new UserNotFound());

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD)->shouldReturn(false);
    }

    function it_doesnt_log_in_when_the_password_is_incorrect_for_the_returned_user(
        PasswordHashingStrategy $passwordHashingStrategy
    )
    {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_WRONG_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->login(self::TEST_USERNAME, self::TEST_WRONG_PASSWORD)->shouldReturn(false);
    }

    function it_doesnt_log_in_if_a_user_is_already_logged_in(
        Session $session
    )
    {
        $session->userIsLoggedIn()->shouldBeCalled()->willReturn(true);

        $this->shouldThrow(new UserAlreadyLoggedIn())->during('login', [self::TEST_USERNAME, self::TEST_PASSWORD]);
    }

    function it_logs_in_when_the_password_is_correct_for_the_returned_user(
        PasswordHashingStrategy $passwordHashingStrategy,
        Session $session,
        AuthUser $user
    )
    {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(true);

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD)->shouldReturn(true);
    }

    function it_delegates_session_management_to_the_session_object_on_login(
        PasswordHashingStrategy $passwordHashingStrategy,
        AuthUser $user,
        Session $session
    )
    {
        $passwordHashingStrategy->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)->willReturn(true);

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_set_up_the_session_when_login_fails_due_to_user_not_found(
        AuthUserGateway $userGateway,
        Session $session
    )
    {
        $userGateway->findUserByUsername(self::TEST_USERNAME)->willThrow(new UserNotFound());

        $session->onSuccessfulAuthentication(Argument::any())->shouldNotBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_set_up_the_session_when_login_fails_due_to_bad_password(
        AuthUserGateway $userGateway,
        AuthUser $user,
        Session $session
    )
    {
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
    )
    {
        $session->userHasAuthenticatedThisSession()->willReturn(false);
        $this->hasAuthenticatedThisSession()->shouldReturn(false);

        $session->userHasAuthenticatedThisSession()->willReturn(true);
        $this->hasAuthenticatedThisSession()->shouldReturn(true);
    }

    function it_takes_a_writeback_gateway_to_write_successful_logins_back_to(
        PasswordHashingStrategy $passwordHashingStrategy,
        WriteBackAuthUserGateway $writeBackAuthUserGateway,
        Session $session,
        AuthUser $user
    )
    {
        $passwordHashingStrategy->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)->willReturn(true);
        $passwordHashingStrategy->hashPassword(self::TEST_PASSWORD)->willReturn(self::TEST_PASSWORD_NEW_HASH);

        $writeBackAuthUserGateway->getPasswordHashingStrategy()->willReturn($passwordHashingStrategy);
        $writeBackAuthUserGateway->saveUser($user, self::TEST_PASSWORD_NEW_HASH)->shouldBeCalled();

        $this->setWriteBackAuthUserGateway($writeBackAuthUserGateway);

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_call_the_writeback_gateway_if_login_fails(
        WriteBackAuthUserGateway $writeBackAuthUserGateway,
        AuthUser $user
    )
    {
        $writeBackAuthUserGateway->saveUser($user, self::TEST_PASSWORD_NEW_HASH)->shouldNotBeCalled();

        $this->setWriteBackAuthUserGateway($writeBackAuthUserGateway);

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD);
    }

    function it_doesnt_tell_the_remembered_login_service_to_remember_login_when_login_is_successful_and_remember_me_isnt_checked(
        PasswordHashingStrategy $passwordHashingStrategy,
        RememberedLoginService $rememberedLoginService,
        Session $session,
        AuthUser $user
    )
    {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(true);

        $rememberedLoginService->attemptToLoadRememberedLogin()->shouldBeCalled();
        $rememberedLoginService->rememberLogin($user)->shouldNotBeCalled();

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD, false);
    }

    function it_doesnt_tell_the_remembered_login_service_to_remember_login_when_login_is_unsuccessful(
        PasswordHashingStrategy $passwordHashingStrategy,
        RememberedLoginService $rememberedLoginService,
        AuthUser $user
    )
    {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_WRONG_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(false);

        $rememberedLoginService->attemptToLoadRememberedLogin()->shouldBeCalled();
        $rememberedLoginService->rememberLogin($user)->shouldNotBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_WRONG_PASSWORD, true);
    }

    function it_tells_the_remembered_login_service_to_remember_login_when_login_is_successful_and_remember_me_checked(
        PasswordHashingStrategy $passwordHashingStrategy,
        RememberedLoginService $rememberedLoginService,
        Session $session,
        AuthUser $user
    )
    {
        $passwordHashingStrategy
            ->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(true);

        $rememberedLoginService->attemptToLoadRememberedLogin()->shouldBeCalled();
        $rememberedLoginService->rememberLogin($user)->shouldBeCalled();

        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $this->login(self::TEST_USERNAME, self::TEST_PASSWORD, true);
    }

    function it_throws_an_exception_if_reathentication_is_attempted_with_noone_logged_in(
        Session $session
    ) {
        $session->userIsLoggedIn()->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(new NoUserLoggedIn())->during('reAuthenticateCurrentUser', [self::TEST_PASSWORD]);
    }

    function it_returns_false_if_a_user_reauthenticates_with_the_wrong_password(
        PasswordHashingStrategy $passwordHashingStrategy,
        Session $session
    ) {
        $session->userIsLoggedIn()->shouldBeCalled()->willReturn(true);
        $session->getLoggedInUsername()->shouldBeCalled()->willReturn(self::TEST_USERNAME);

        $passwordHashingStrategy
            ->verifyPassword(self::TEST_WRONG_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->reAuthenticateCurrentUser(self::TEST_WRONG_PASSWORD)->shouldReturn(false);
    }

    function it_returns_true_if_a_user_reauthenticates_with_the_correct_password(
        PasswordHashingStrategy $passwordHashingStrategy,
        Session $session,
        AuthUser $user
    ) {
        $session->userIsLoggedIn()->shouldBeCalled()->willReturn(true);
        $session->getLoggedInUsername()->shouldBeCalled()->willReturn(self::TEST_USERNAME);
        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $passwordHashingStrategy
            ->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(true);

        $this->reAuthenticateCurrentUser(self::TEST_PASSWORD)->shouldReturn(true);
    }

    function it_rewrites_the_remembered_login_if_a_user_reauthenticates_with_the_correct_password(
        PasswordHashingStrategy $passwordHashingStrategy,
        Session $session,
        AuthUser $user,
        RememberedLoginService $rememberedLoginService
    ) {
        $session->userIsLoggedIn()->shouldBeCalled()->willReturn(true);
        $session->getLoggedInUsername()->shouldBeCalled()->willReturn(self::TEST_USERNAME);
        $session->onSuccessfulAuthentication($user)->shouldBeCalled();

        $passwordHashingStrategy
            ->verifyPassword(self::TEST_PASSWORD, self::TEST_PASSWORD_HASH)
            ->shouldBeCalled()
            ->willReturn(true);

        $rememberedLoginService->attemptToLoadRememberedLogin()->shouldBeCalled();
        $rememberedLoginService->rememberLogin($user)->shouldBeCalled();

        $this->reAuthenticateCurrentUser(self::TEST_PASSWORD);
    }
}
