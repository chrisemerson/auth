<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Users\User;
use CEmerson\Clock\Clock;

final class AceAuthSession implements Session
{
    const SESSION_ID_REGENERATION_INTERVAL = 300;

    const SESSION_CANARY_NAME = 'aceauth.canary';
    const SESSION_AUTH_THIS_SESSION_NAME = 'aceauth.auththissession';
    const SESSION_CURRENT_USER_NAME = 'aceauth.currentuser';

    /** @var SessionGateway */
    private $sessionGateway;

    /** @var bool */
    private $initialised = false;

    /** @var Clock */
    private $clock;

    public function __construct(SessionGateway $sessionGateway, Clock $clock)
    {
        $this->sessionGateway = $sessionGateway;
        $this->clock = $clock;
    }

    public function init()
    {
        $this->sessionGateway->start();

        $this->regenerateSessionIfRequired();

        $this->initialised = true;
    }

    private function checkSessionStarted()
    {
        if (!$this->initialised) {
            $this->init();
        }
    }

    public function userIsLoggedIn(): bool
    {
        $this->checkSessionStarted();

        return $this->sessionGateway->exists(self::SESSION_CURRENT_USER_NAME);
    }

    public function getLoggedInUsername(): string
    {
        $this->checkSessionStarted();

        return $this->sessionGateway->read(self::SESSION_CURRENT_USER_NAME);
    }

    public function userHasAuthenticatedThisSession(): bool
    {
        $this->checkSessionStarted();

        return (
            $this->sessionGateway->exists(self::SESSION_AUTH_THIS_SESSION_NAME)
            && $this->sessionGateway->read(self::SESSION_AUTH_THIS_SESSION_NAME) == 1
        );
    }

    public function onSuccessfulAuthentication(User $authenticatedUser)
    {
        $this->checkSessionStarted();

        $this->sessionGateway->write(self::SESSION_CURRENT_USER_NAME, $authenticatedUser->getUsername());
        $this->sessionGateway->write(self::SESSION_AUTH_THIS_SESSION_NAME, 1);

        $this->regenerateSession();
    }

    public function deleteAceAuthSessionInfo()
    {
        $this->checkSessionStarted();

        $this->sessionGateway->delete(self::SESSION_CURRENT_USER_NAME);
        $this->sessionGateway->delete(self::SESSION_AUTH_THIS_SESSION_NAME);
        $this->sessionGateway->delete(self::SESSION_CANARY_NAME);
    }

    private function regenerateSessionIfRequired()
    {
        if ($this->sessionRequiresRegeneration()) {
            $this->regenerateSession();
        }
    }

    private function sessionRequiresRegeneration()
    {
        return !$this->sessionCanaryExists() || $this->sessionCanaryIndicatesRegenerationRequired();
    }

    private function sessionCanaryExists()
    {
        return $this->sessionGateway->exists(self::SESSION_CANARY_NAME);
    }

    private function sessionCanaryIndicatesRegenerationRequired()
    {
        return
            $this->sessionGateway->read(self::SESSION_CANARY_NAME) <= (
                $this->getUnixTimestamp() - self::SESSION_ID_REGENERATION_INTERVAL
            );
    }

    private function regenerateSession()
    {
        $this->sessionGateway->regenerate();

        $this->writeNewSessionCanary();
    }

    private function writeNewSessionCanary()
    {
        return $this->sessionGateway->write(self::SESSION_CANARY_NAME, $this->getUnixTimestamp());
    }

    private function getUnixTimestamp(): int
    {
        return intval($this->clock->getDateTime()->format('U'));
    }
}
