<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\RememberedLogins;

use CEmerson\Auth\Cookie\CookieGateway;
use CEmerson\Auth\RememberedLogins\RememberedLoginFactory;
use CEmerson\Auth\RememberedLogins\RememberedLoginGateway;
use CEmerson\Auth\RememberedLogins\RememberedLoginService;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUserGateway;
use PhpSpec\ObjectBehavior;

class RememberedLoginServiceSpec extends ObjectBehavior
{
    const TEST_SELECTOR = 'test_selector';
    const TEST_TOKEN = 'test_token';

    function let(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        Session $session,
        AuthUserGateway $authUserGateway,
        RememberedLoginFactory $rememberedLoginFactory
    ) {
        $this->beConstructedWith(
            $rememberedLoginGateway,
            $cookieGateway,
            $session,
            $authUserGateway,
            $rememberedLoginFactory
        );
    }





    function it_deletes_remembered_logins_on_request(CookieGateway $cookieGateway)
    {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(false);
        $cookieGateway->delete(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled();

        $this->deleteRememberedLogin();
    }

    function it_deletes_remembered_logins_from_database_when_cookie_value_is_set(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway
    ) {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(true);
        $cookieGateway->read(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(
            self::TEST_SELECTOR . RememberedLoginService::DELIMITER . self::TEST_TOKEN
        );

        $rememberedLoginGateway->deleteRememberedLoginBySelector(self::TEST_SELECTOR)->shouldBeCalled();

        $cookieGateway->delete(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled();

        $this->deleteRememberedLogin();
    }
}
