<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

use CEmerson\Auth\Cookie\CookieGateway;
use CEmerson\Auth\Exceptions\RememberedLoginNotFound;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
use CEmerson\Clock\Clock;
use DateTimeImmutable;

class RememberedLoginService
{
    /** @var RememberedLoginGateway */
    private $rememberedLoginGateway;

    /** @var CookieGateway */
    private $cookieGateway;

    /** @var Session */
    private $session;

    /** @var AuthUserGateway */
    private $userGateway;

    /** @var RememberedLoginFactory */
    private $rememberedLoginFactory;

    /** @var Clock */
    private $clock;

    const COOKIE_SELECTOR_NAME = 'cemerson.auth.rememberme';

    const DELIMITER = ':';
    //Default length for remembered login - 30 days

    private $rememberedLoginTTL = 30 * 24 * 60 * 60;
    //Ensure the token stays available in the database for slightly longer than the cookie is valid for

    private $storedRememberedLoginGracePeriod = 60 * 60;

    public function __construct(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        Session $session,
        AuthUserGateway $userGateway,
        RememberedLoginFactory $rememberedLoginFactory,
        Clock $clock
    ) {
        $this->rememberedLoginGateway = $rememberedLoginGateway;
        $this->cookieGateway = $cookieGateway;
        $this->session = $session;
        $this->userGateway = $userGateway;
        $this->rememberedLoginFactory = $rememberedLoginFactory;
        $this->clock = $clock;
    }

    public function setRememberedLoginTTL(int $rememberedLoginTTL)
    {
        $this->rememberedLoginTTL = $rememberedLoginTTL;
    }

    public function setStoredRememberedLoginGracePeriod(int $storedRememberedLoginGracePeriod)
    {
        $this->storedRememberedLoginGracePeriod = $storedRememberedLoginGracePeriod;
    }

    public function rememberLogin(AuthUser $user)
    {
        $selector = $this->generateToken(20);
        $token = $this->generateToken(20);

        $expiryTimestamp =
            (int) $this->clock->getDateTime()->format('U')
            + $this->rememberedLoginTTL
            + $this->storedRememberedLoginGracePeriod;

        $expiryDateTime = new DateTimeImmutable('@' . $expiryTimestamp);

        $rememberedLogin = $this->rememberedLoginFactory->createRememberedLogin(
            $user->getUsername(),
            $selector,
            $this->hashToken($token),
            $expiryDateTime
        );

        $this->rememberedLoginGateway->saveRememberedLogin($rememberedLogin);

        $this->cookieGateway->write(
            self::COOKIE_SELECTOR_NAME,
            $selector . self::DELIMITER . $token,
            $this->rememberedLoginTTL
        );
    }

    public function loadRememberedLoginFromCookie()
    {
        if ($this->cookieGateway->exists(self::COOKIE_SELECTOR_NAME)) {
            $cookieValue = $this->cookieGateway->read(self::COOKIE_SELECTOR_NAME);
            list($selector, $token) = explode(self::DELIMITER, $cookieValue);

            try {
                $this->setCurrentUserFromCookie($selector, $token);
            } catch (RememberedLoginNotFound $e) {
            }
        }
    }

    private function setCurrentUserFromCookie($selector, $token)
    {
        $rememberedLogin = $this->rememberedLoginGateway->findRememberedLoginBySelector($selector);

        if (hash_equals($rememberedLogin->getToken(), $this->hashToken($token))) {
            $currentUser = $this->userGateway->findUserByUsername($rememberedLogin->getUsername());

            $this->session->setCurrentlyLoggedInUser($currentUser);
        }
    }

    public function deleteRememberedLogin()
    {
        if ($this->cookieGateway->exists(self::COOKIE_SELECTOR_NAME)) {
            $cookieValue = $this->cookieGateway->read(self::COOKIE_SELECTOR_NAME);
            $selector = explode(self::DELIMITER, $cookieValue)[0];

            $this->rememberedLoginGateway->deleteRememberedLoginBySelector($selector);
        }

        $this->cookieGateway->delete(self::COOKIE_SELECTOR_NAME);
    }

    private function generateToken(int $length): string
    {
        return bin2hex(random_bytes($length));
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}
