<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts\Session;

use CEmerson\Auth\User\AuthUser;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class AuthSession implements Session, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const SESSION_ID_REGENERATION_INTERVAL = 300;

    private const SESSION_CANARY_NAME = 'cemerson_auth_canary';
    private const SESSION_AUTH_THIS_SESSION_NAME = 'cemerson_auth_auththissession';
    private const SESSION_CURRENT_USER_NAME = 'cemerson_auth_currentuser';

    /** @var SessionGateway */
    private $sessionGateway;

    /** @var bool */
    private $initialised = false;

    /** @var ClockInterface */
    private $clock;

    public function __construct(SessionGateway $sessionGateway, ClockInterface $clock)
    {
        $this->sessionGateway = $sessionGateway;
        $this->clock = $clock;

        $this->setLogger(new NullLogger());
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

    public function userHasAuthenticatedThisSession(): bool
    {
        $this->checkSessionStarted();

        return (
            $this->sessionGateway->exists(self::SESSION_AUTH_THIS_SESSION_NAME)
            && $this->sessionGateway->read(self::SESSION_AUTH_THIS_SESSION_NAME) == 1
        );
    }

    public function setCurrentlyLoggedInUser(AuthUser $currentUser)
    {
        $this->checkSessionStarted();

        $this->sessionGateway->write(self::SESSION_CURRENT_USER_NAME, $currentUser->getUsername());
    }

    public function onSuccessfulAuthentication(AuthUser $authenticatedUser)
    {
        $this->setCurrentlyLoggedInUser($authenticatedUser);

        $this->sessionGateway->write(self::SESSION_AUTH_THIS_SESSION_NAME, 1);

        $this->regenerateSession();
    }

    public function deleteAuthSessionInfo()
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
        return intval($this->clock->now()->format('U'));
    }
}
