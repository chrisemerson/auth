<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Session\AceAuthSession;
use CEmerson\AceAuth\Session\SessionGateway;
use CEmerson\AceAuth\Users\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AceAuthSessionSpec extends ObjectBehavior
{
    function let(SessionGateway $sessionGateway)
    {
        $this->beConstructedWith($sessionGateway);

        $sessionGateway->start()->willReturn();
        $sessionGateway->exists(AceAuthSession::SESSION_CANARY_NAME)->willReturn(true);
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->willReturn();
        $sessionGateway->write(AceAuthSession::SESSION_CANARY_NAME, Argument::type('int'))->willReturn();
        $sessionGateway->regenerate()->willReturn();
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
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn(time() - AceAuthSession::SESSION_ID_REGENERATION_INTERVAL);
        $sessionGateway->regenerate()->shouldBeCalled();

        $this->init();
    }

    function it_should_not_regenerate_the_session_id_when_the_canary_indicates_5_minutes_have_not_passed(SessionGateway $sessionGateway)
    {
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn(
            time() - (ceil(AceAuthSession::SESSION_ID_REGENERATION_INTERVAL / 2))
        );

        $sessionGateway->regenerate()->shouldNotBeCalled();

        $this->init();
    }

    function it_writes_a_new_canary_when_the_session_regenerates(SessionGateway $sessionGateway)
    {
        $sessionGateway->exists(AceAuthSession::SESSION_CANARY_NAME)->shouldBeCalled()->willReturn(false);
        $sessionGateway->regenerate()->shouldBeCalled();
        $sessionGateway->write(AceAuthSession::SESSION_CANARY_NAME, time())->shouldBeCalled();

        $this->init();
    }

    function it_should_store_the_currently_logged_in_user_on_successful_authentication(
        SessionGateway $sessionGateway,
        User $user
    ) {
        $user->getUsername()->willReturn('test_username');
        $sessionGateway->write('currentuser', 'test_username')->shouldBeCalled();

        $this->onSuccessfulAuthentication($user);
    }

    function it_regenerates_the_session_id_on_successful_authentication(
        SessionGateway $sessionGateway,
        User $user
    ) {
        $sessionGateway->read(AceAuthSession::SESSION_CANARY_NAME)->willReturn(time());

        $user->getUsername()->willReturn('test_username');
        $sessionGateway->write('currentuser', 'test_username')->shouldBeCalled();

        $sessionGateway->regenerate()->shouldBeCalled();
        $sessionGateway->write(AceAuthSession::SESSION_CANARY_NAME, Argument::type('int'))->shouldBeCalled();

        $this->onSuccessfulAuthentication($user);
    }

    function it_destroys_the_session_when_told_to(SessionGateway $sessionGateway)
    {
        $sessionGateway->destroy()->shouldBeCalled();

        $this->destroySession();
    }
}
