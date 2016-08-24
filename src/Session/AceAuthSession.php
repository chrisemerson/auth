<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

use CEmerson\AceAuth\Users\User;

final class AceAuthSession implements Session
{
    const SESSION_ID_REGENERATION_INTERVAL = 300;
    const SESSION_CANARY_NAME = 'canary';

    /** @var SessionGateway */
    private $sessionGateway;

    /** @var bool */
    private $sessionStarted = false;

    public function __construct(SessionGateway $sessionGateway)
    {
        $this->sessionGateway = $sessionGateway;
    }

    public function init()
    {
        $this->sessionGateway->start();

        $this->regenerateSessionIfRequired();

        $this->sessionStarted = true;
    }

    private function checkSessionStarted()
    {
        if (!$this->sessionStarted) {
            $this->init();
        }
    }

    public function onSuccessfulAuthentication(User $authenticatedUser)
    {
        $this->checkSessionStarted();

        $this->sessionGateway->write('currentuser', $authenticatedUser->getUsername());
        $this->regenerateSession();
    }

    public function destroySession()
    {
        $this->checkSessionStarted();

        $this->sessionGateway->destroy();
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
            $this->sessionGateway->read(self::SESSION_CANARY_NAME) <= time() - self::SESSION_ID_REGENERATION_INTERVAL;
    }

    private function regenerateSession()
    {
        $this->sessionGateway->regenerate();

        $this->writeNewSessionCanary();
    }

    private function writeNewSessionCanary()
    {
        return $this->sessionGateway->write(self::SESSION_CANARY_NAME, time());
    }
}
