<?php declare(strict_types = 1);

namespace spec\CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Session\AceAuthSession;
use CEmerson\AceAuth\Session\SessionGateway;
use PhpSpec\ObjectBehavior;

class AceAuthSessionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AceAuthSession::class);
    }

    function let(SessionGateway $sessionGateway)
    {
        $this->beConstructedWith($sessionGateway);
    }
}
