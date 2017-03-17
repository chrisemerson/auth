<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\RememberedLogins;

use CEmerson\Auth\Cookie\CookieGateway;
use CEmerson\Auth\RememberedLogins\RememberedLogin;
use CEmerson\Auth\RememberedLogins\RememberedLoginFactory;
use CEmerson\Auth\RememberedLogins\RememberedLoginGateway;
use CEmerson\Auth\RememberedLogins\RememberedLoginService;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
use CEmerson\Clock\Clock;
use DateTimeImmutable;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RememberedLoginServiceSpec extends ObjectBehavior
{
    const TEST_SELECTOR = 'test_selector';
    const TEST_TOKEN = 'test_token';

    const TEST_USERNAME = 'test_remembered_username';

    function let(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        Session $session,
        AuthUserGateway $authUserGateway,
        RememberedLoginFactory $rememberedLoginFactory,
        Clock $clock
    ) {
        $this->beConstructedWith(
            $rememberedLoginGateway,
            $cookieGateway,
            $session,
            $authUserGateway,
            $rememberedLoginFactory,
            $clock
        );
    }

    function it_sets_the_appropriate_cookie_and_gateway_values_when_remembering_a_user(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        RememberedLoginFactory $rememberedLoginFactory,
        RememberedLogin $rememberedLogin,
        AuthUser $user,
        Clock $clock,
        DateTimeImmutable $dateTime
    ) {
        $user->getUsername()->willReturn(self::TEST_USERNAME);

        $clock->getDateTime()->willReturn($dateTime);
        $dateTime->format('U')->willReturn('1000000');

        $rememberedLoginFactory
            ->createRememberedLogin(
                self::TEST_USERNAME,
                Argument::type('string'),
                Argument::type('string'),
                Argument::that(function ($arg) {
                    return $arg->format('U') == 1000105;
                })
            )
            ->shouldBeCalled()
            ->willReturn($rememberedLogin);

        $rememberedLoginGateway->saveRememberedLogin($rememberedLogin)->shouldBeCalled();

        $cookieGateway
            ->write(
                RememberedLoginService::COOKIE_SELECTOR_NAME,
                Argument::type('string'),
                100
            )
            ->shouldBeCalled();

        $this->setRememberedLoginTTL(100);
        $this->setStoredRememberedLoginGracePeriod(5);

        $this->rememberLogin($user);
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
