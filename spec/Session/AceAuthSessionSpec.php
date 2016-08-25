<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Session\AceAuthSession;
use CEmerson\AceAuth\Session\SessionGateway;
use CEmerson\AceAuth\Users\User;
use CultuurNet\Clock\Clock;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AceAuthSessionSpec extends ObjectBehavior
{
    const TEST_CURRENT_TIMESTAMP = 1472122757;

    function let(SessionGateway $sessionGateway, Clock $clock, DateTimeImmutable $dateTime)
    {
        $this->beConstructedWith($sessionGateway, $clock);

        $sessionGateway->start()->willReturn();
        $sessionGateway->exists(AceAuthSession::SESSION_CANARY_NAME)->willReturn(true);
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->willReturn();
        $sessionGateway->write(AceAuthSession::SESSION_CANARY_NAME, Argument::type('int'))->willReturn();
        $sessionGateway->regenerate()->willReturn();

        $dateTime->format('U')->willReturn(self::TEST_CURRENT_TIMESTAMP);
        $clock->getDateTime()->willReturn($dateTime);
    }

    function it_starts_the_session_on_initialisation(SessionGateway $sessionGateway)
    {
        $sessionGateway->start()->shouldBeCalled();

        $this->init();
    }

    function it_should_regenerate_the_session_id_when_no_canary_is_set_on_initialisation(SessionGateway $sessionGateway)
    {
        $sessionGateway->exists(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn(false);
        $sessionGateway->regenerate()->shouldBeCalled();

        $this->init();
    }

    function it_should_regenerate_the_session_id_when_the_canary_indicates_5_minutes_have_passed(SessionGateway $sessionGateway)
    {
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn(self::TEST_CURRENT_TIMESTAMP - AceAuthSession::SESSION_ID_REGENERATION_INTERVAL);
        $sessionGateway->regenerate()->shouldBeCalled();

        $this->init();
    }

    function it_should_not_regenerate_the_session_id_when_the_canary_indicates_5_minutes_have_not_passed(SessionGateway $sessionGateway)
    {
        $halfAnIntervalAgo = self::TEST_CURRENT_TIMESTAMP - (ceil(AceAuthSession::SESSION_ID_REGENERATION_INTERVAL / 2));

        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn($halfAnIntervalAgo);

        $sessionGateway->regenerate()->shouldNotBeCalled();

        $this->init();
    }

    function it_writes_a_new_canary_when_the_session_regenerates(SessionGateway $sessionGateway)
    {
        $sessionGateway->exists(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn(false);
        $sessionGateway->regenerate()->shouldBeCalled();
        $sessionGateway->write(AceAuthSession::SESSION_CANARY_NAME, self::TEST_CURRENT_TIMESTAMP)->shouldBeCalled();

        $this->init();
    }

    function it_should_store_the_currently_logged_in_user_on_successful_authentication(
        SessionGateway $sessionGateway,
        User $user
    ) {
        $user->getUsername()->willReturn('test_username');
        $sessionGateway->write(AceAuthSession::SESSION_CURRENT_USER_NAME, 'test_username')->shouldBeCalled();
        $sessionGateway->write(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME, 1)->shouldBeCalled();

        $this->onSuccessfulAuthentication($user);
    }

    function it_regenerates_the_session_id_on_successful_authentication(
        SessionGateway $sessionGateway,
        User $user
    ) {
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->willReturn(self::TEST_CURRENT_TIMESTAMP);

        $user->getUsername()->willReturn('test_username');
        $sessionGateway->write(AceAuthSession::SESSION_CURRENT_USER_NAME, 'test_username')->shouldBeCalled();
        $sessionGateway->write(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME, 1)->shouldBeCalled();

        $sessionGateway->regenerate()->shouldBeCalled();
        $sessionGateway->write(AceAuthSession::SESSION_CANARY_NAME, Argument::type('int'))->shouldBeCalled();

        $this->onSuccessfulAuthentication($user);
    }

    function it_destroys_the_session_when_told_to(SessionGateway $sessionGateway)
    {
        $sessionGateway->delete(AceAuthSession::SESSION_CURRENT_USER_NAME)->shouldBeCalled();
        $sessionGateway->delete(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME)->shouldBeCalled();
        $sessionGateway->delete(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled();

        $this->deleteAceAuthSessionInfo();
    }

    function it_should_delegate_queries_about_whether_the_user_is_logged_in_to_the_session_gateway(
        SessionGateway $sessionGateway
    ) {
        $sessionGateway->exists(AceAuthSession::SESSION_CURRENT_USER_NAME)->shouldBeCalled();

        $this->userIsLoggedIn();
    }

    function it_should_delegate_queries_about_the_current_user_to_the_session_gateway(SessionGateway $sessionGateway)
    {
        $sessionGateway->read(AceAuthSession::SESSION_CURRENT_USER_NAME)->shouldBeCalled()->willReturn('test_username');

        $this->getLoggedInUsername()->shouldReturn('test_username');
    }

    function it_should_say_the_user_has_not_authenticated_this_session_if_session_var_is_not_set(
        SessionGateway $sessionGateway
    ) {
        $sessionGateway->exists(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME)->willReturn(false);

        $this->userHasAuthenticatedThisSession()->shouldReturn(false);
    }

    function it_should_say_the_user_has_not_authenticated_this_session_if_session_var_is_set_but_not_1(
        SessionGateway $sessionGateway
    ) {
        $sessionGateway->exists(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME)->willReturn(true);
        $sessionGateway->read(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME)->willReturn(0);

        $this->userHasAuthenticatedThisSession()->shouldReturn(false);
    }

    function it_should_say_the_user_has_authenticated_this_session_if_session_var_is_set_and_1(
        SessionGateway $sessionGateway
    ) {
        $sessionGateway->exists(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME)->willReturn(true);
        $sessionGateway->read(AceAuthSession::SESSION_AUTH_THIS_SESSION_NAME)->willReturn(1);

        $this->userHasAuthenticatedThisSession()->shouldReturn(true);
    }
}
