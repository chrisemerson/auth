<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\RememberedLogins;

use CEmerson\Auth\RememberedLogins\RememberedLoginTestStub;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;

class RememberedLoginTraitSpec extends ObjectBehavior
{
    const TEST_USERNAME = "test_username";
    const TEST_SELECTOR = "test_selector";
    const TEST_TOKEN = "test_token";

    function let()
    {
        $this->beAnInstanceOf(RememberedLoginTestStub::class);
    }

    function it_retrieves_the_username_passed_in()
    {
        $this->setUsername(self::TEST_USERNAME);
        $this->getUsername()->shouldReturn(self::TEST_USERNAME);
    }

    function it_retrieves_the_selector_passed_in()
    {
        $this->setSelector(self::TEST_SELECTOR);
        $this->getSelector()->shouldReturn(self::TEST_SELECTOR);
    }

    function it_retrieves_the_token_passed_in()
    {
        $this->setToken(self::TEST_USERNAME);
        $this->getToken()->shouldReturn(self::TEST_USERNAME);
    }

    function it_retrieves_the_expiry_passed_in()
    {
        $expiry = new DateTimeImmutable();

        $this->setExpiryDateTime($expiry);
        $this->getExpiryDateTime()->shouldReturn($expiry);
    }
}
