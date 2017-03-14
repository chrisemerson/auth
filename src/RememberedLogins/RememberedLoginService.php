<?php declare(strict_types = 1);

namespace CEmerson\Auth\RememberedLogins;

use CEmerson\Auth\Cookie\CookieGateway;
use CEmerson\Auth\Exceptions\RememberedLoginNotFound;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
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

    const COOKIE_SELECTOR_NAME = 'cemerson.auth.rememberme';
    const DELIMITER = ':';

    private $rememberedLoginTTL = 30 * 24 * 60 * 60;
    private $storedRememberedLoginGracePeriod = 60 * 60;

    public function __construct(
        RememberedLoginGateway $rememberedLoginGateway,
        CookieGateway $cookieGateway,
        Session $session,
        AuthUserGateway $userGateway,
        RememberedLoginFactory $rememberedLoginFactory
    ) {
        $this->rememberedLoginGateway = $rememberedLoginGateway;
        $this->cookieGateway = $cookieGateway;
        $this->session = $session;
        $this->userGateway = $userGateway;
        $this->rememberedLoginFactory = $rememberedLoginFactory;
    }

    public function rememberLogin(AuthUser $user)
    {
        $selector = $this->generateToken(20);
        $token = $this->generateToken(20);

        $expiryDateTime = new DateTimeImmutable(
            '@' . (time() + $this->rememberedLoginTTL + $this->storedRememberedLoginGracePeriod)
        );

        $this->rememberedLoginGateway->saveRememberedLogin(
            $this->rememberedLoginFactory->createRememberedLogin(
                $user->getUsername(),
                $selector,
                $this->hashToken($token),
                $expiryDateTime
            )
        );

        $this->cookieGateway->write(
            self::COOKIE_SELECTOR_NAME,
            $selector . self::DELIMITER . $token,
            $this->rememberedLoginTTL
        );
    }

    public function attemptToLoadRememberedLogin()
    {
        if ($this->cookieGateway->exists(self::COOKIE_SELECTOR_NAME)) {
            $cookieValue = $this->cookieGateway->read(self::COOKIE_SELECTOR_NAME);
            list($selector, $token) = explode(self::DELIMITER, $cookieValue);

            try {
                $rememberedLogin = $this->rememberedLoginGateway->findRememberedLoginBySelector($selector);

                if (hash_equals($rememberedLogin->getToken(), $this->hashToken($token))) {
                    $currentUser = $this->userGateway->findUserByUsername($rememberedLogin->getUsername());

                    $this->session->setCurrentlyLoggedInUser($currentUser);

                    return true;
                }
            } catch (RememberedLoginNotFound $e) {
            }
        }

        return false;
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
