<?php declare(strict_types = 1);

namespace spec\CEmerson\Auth\RememberedLogins;

use CEmerson\Auth\Cookie\CookieGateway;
use CEmerson\Auth\Exceptions\RememberedLoginNotFound;
use CEmerson\Auth\RememberedLogins\RememberedLogin;
use CEmerson\Auth\RememberedLogins\RememberedLoginFactory;
use CEmerson\Auth\RememberedLogins\RememberedLoginGateway;
use CEmerson\Auth\RememberedLogins\RememberedLoginService;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
use CEmerson\Clock\Clock;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
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
        Clock $clock,
        DateTimeImmutable $dateTime
    ) {
        $clock->getDateTime()->willReturn($dateTime);
        $dateTime->getTimestamp()->willReturn(1000000);

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
        $this->runRememberedLoginTest(
            $rememberedLoginGateway,
            $cookieGateway,
            $rememberedLoginFactory,
            $rememberedLogin,
            $user,
            $clock,
            $dateTime,
            30 * 24 * 60 * 60,
            60 * 60
        );
    }

    function it_allows_the_remembered_login_ttl_to_be_overridden(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        RememberedLoginFactory $rememberedLoginFactory,
        RememberedLogin $rememberedLogin,
        AuthUser $user,
        Clock $clock,
        DateTimeImmutable $dateTime
    ) {
        $this->runRememberedLoginTest(
            $rememberedLoginGateway,
            $cookieGateway,
            $rememberedLoginFactory,
            $rememberedLogin,
            $user,
            $clock,
            $dateTime,
            24 * 60 * 60,
            60 * 60
        );
    }

    function it_allows_the_persisted_remembered_login_grace_period_to_be_overridden(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        RememberedLoginFactory $rememberedLoginFactory,
        RememberedLogin $rememberedLogin,
        AuthUser $user,
        Clock $clock,
        DateTimeImmutable $dateTime
    ) {
        $this->runRememberedLoginTest(
            $rememberedLoginGateway,
            $cookieGateway,
            $rememberedLoginFactory,
            $rememberedLogin,
            $user,
            $clock,
            $dateTime,
            30 * 24 * 60 * 60,
            5
        );
    }

    function it_loads_a_remembered_login_from_a_cookie_if_present(
        CookieGateway $cookieGateway,
        RememberedLoginGateway $rememberedLoginGateway,
        RememberedLogin $rememberedLogin,
        AuthUserGateway $authUserGateway,
        AuthUser $authUser,
        Session $session
    ) {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(true);

        $cookieGateway
            ->read(RememberedLoginService::COOKIE_SELECTOR_NAME)
            ->shouldBeCalled()
            ->willReturn(self::TEST_SELECTOR . ':' . self::TEST_TOKEN);

        $rememberedLoginGateway
            ->findRememberedLoginBySelector(self::TEST_SELECTOR)
            ->shouldBeCalled()
            ->willReturn($rememberedLogin);

        $rememberedLogin->getExpiryDateTime()->willReturn(new DateTimeImmutable('@2000000'));
        $rememberedLogin->getToken()->willReturn(hash('sha256', self::TEST_TOKEN));
        $rememberedLogin->getUsername()->willReturn(self::TEST_USERNAME);

        $authUserGateway->findUserByUsername(self::TEST_USERNAME)->willReturn($authUser);

        $session->setCurrentlyLoggedInUser($authUser)->shouldBeCalled();

        $this->loadRememberedLoginFromCookie();
    }

    function it_doesnt_set_the_current_user_if_the_cookie_doesnt_exist(
        CookieGateway $cookieGateway,
        Session $session
    ) {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(false);

        $session->setCurrentlyLoggedInUser(Argument::any())->shouldNotBeCalled();

        $this->loadRememberedLoginFromCookie();
    }

    function it_doesnt_set_the_current_user_if_the_cookie_exists_but_doesnt_contain_a_valid_remembered_login(
        CookieGateway $cookieGateway,
        Session $session,
        RememberedLoginGateway $rememberedLoginGateway
    ) {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(true);

        $cookieGateway
            ->read(RememberedLoginService::COOKIE_SELECTOR_NAME)
            ->shouldBeCalled()
            ->willReturn(self::TEST_SELECTOR . ':' . self::TEST_TOKEN);

        $rememberedLoginGateway->findRememberedLoginBySelector(self::TEST_SELECTOR)->shouldBeCalled()->willThrow(
            new RememberedLoginNotFound()
        );

        $session->setCurrentlyLoggedInUser(Argument::any())->shouldNotBeCalled();

        $this->loadRememberedLoginFromCookie();
    }

    function it_doesnt_set_the_current_user_if_the_cookie_exists_and_remembered_login_exists_but_token_is_incorrect(
        CookieGateway $cookieGateway,
        Session $session,
        RememberedLoginGateway $rememberedLoginGateway,
        RememberedLogin $rememberedLogin
    ) {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(true);

        $cookieGateway
            ->read(RememberedLoginService::COOKIE_SELECTOR_NAME)
            ->shouldBeCalled()
            ->willReturn(self::TEST_SELECTOR . ':' . self::TEST_TOKEN);

        $rememberedLoginGateway
            ->findRememberedLoginBySelector(self::TEST_SELECTOR)
            ->shouldBeCalled()
            ->willReturn($rememberedLogin);

        $rememberedLogin->getExpiryDateTime()->willReturn(new DateTimeImmutable('@2000000'));
        $rememberedLogin->getToken()->willReturn('somethingwrong');

        $session->setCurrentlyLoggedInUser(Argument::any())->shouldNotBeCalled();

        $this->loadRememberedLoginFromCookie();
    }

    function it_doesnt_set_the_current_user_if_the_cookie_exists_but_remembered_login_has_expired(
        CookieGateway $cookieGateway,
        Session $session,
        RememberedLoginGateway $rememberedLoginGateway,
        RememberedLogin $rememberedLogin,
        Clock $clock,
        DateTimeImmutable $dateTime
    ) {
        $cookieGateway->exists(RememberedLoginService::COOKIE_SELECTOR_NAME)->shouldBeCalled()->willReturn(true);

        $cookieGateway
            ->read(RememberedLoginService::COOKIE_SELECTOR_NAME)
            ->shouldBeCalled()
            ->willReturn(self::TEST_SELECTOR . ':' . self::TEST_TOKEN);

        $rememberedLoginGateway
            ->findRememberedLoginBySelector(self::TEST_SELECTOR)
            ->shouldBeCalled()
            ->willReturn($rememberedLogin);

        $clock->getDateTime()->willReturn($dateTime);
        $dateTime->getTimestamp()->willReturn(1000000);

        $rememberedLogin->getExpiryDateTime()->willReturn(new DateTimeImmutable('@500000'));
        $rememberedLogin->getToken()->willReturn(hash('sha256', self::TEST_TOKEN));

        $session->setCurrentlyLoggedInUser(Argument::any())->shouldNotBeCalled();

        $this->loadRememberedLoginFromCookie();
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

    public static function convertDateIntervalToSeconds(DateInterval $dateInterval)
    {
        return $dateInterval->days * 86400 + $dateInterval->h * 3600 + $dateInterval->i * 60 + $dateInterval->s;
    }

    private function runRememberedLoginTest(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        RememberedLoginFactory $rememberedLoginFactory,
        RememberedLogin $rememberedLogin,
        AuthUser $user,
        Clock $clock,
        DateTimeImmutable $dateTime,
        int $expectedTTL,
        int $expectedGracePeriod
    ) {
        $user->getUsername()->willReturn(self::TEST_USERNAME);

        $clock->getDateTime()->willReturn($dateTime);

        $dateTime->getTimestamp()->willReturn(1000000);

        $rememberedLoginFactory
            ->createRememberedLogin(
                self::TEST_USERNAME,
                Argument::type('string'),
                Argument::type('string'),
                Argument::that(function ($arg) use ($expectedTTL, $expectedGracePeriod) {
                    return
                        $arg instanceof DateTimeInterface
                        && $arg->getTimestamp() == 1000000 + $expectedTTL + $expectedGracePeriod;
                })
            )
            ->shouldBeCalled()
            ->willReturn($rememberedLogin);

        $rememberedLoginGateway->saveRememberedLogin($rememberedLogin)->shouldBeCalled();

        $cookieGateway
            ->write(
                RememberedLoginService::COOKIE_SELECTOR_NAME,
                Argument::type('string'),
                Argument::that(function ($arg) use ($expectedTTL) {
                    return
                        $arg instanceof DateTimeInterface
                        && $arg->getTimestamp() == 1000000 + $expectedTTL;
                })
            )
            ->shouldBeCalled();

        $this->setRememberedLoginTTL($expectedTTL);
        $this->setStoredRememberedLoginGracePeriod($expectedGracePeriod);

        $this->setStoredRememberedLoginGracePeriod($expectedGracePeriod);

        $this->rememberLogin($user);
    }
}
