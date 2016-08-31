<?php declare(strict_types = 1);

namespace CEmerson\Auth;

use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class Auth implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var AuthUserGateway */
    private $userGateway;

    /** @var Session */
    private $session;

    public function __construct(AuthUserGateway $userGateway, Session $session)
    {
        $this->userGateway = $userGateway;
        $this->session = $session;

        $this->setLogger(new NullLogger());
    }

    public function login(string $username, string $password, bool $rememberMe = false): bool
    {
        try {
            $user = $this->userGateway->findUserByUsername($username);
        } catch (UserNotFound $e) {
            return false;
        }

        if ($this->verifyPassword($user, $password)) {
            $this->session->onSuccessfulAuthentication($user);

            return true;
        }

        return false;
    }

    public function verifyPassword(AuthUser $user, string $password): bool
    {
        $passwordHashingStrategy = $user->getPasswordHashingStrategy();
        $passwordHash = $user->getPasswordHash();

        return $passwordHashingStrategy->verifyPassword($password, $passwordHash);
    }

    public function logout()
    {
        $this->session->deleteAuthSessionInfo();
    }

    public function isLoggedIn(): bool
    {
        return $this->session->userIsLoggedIn();
    }

    public function getCurrentUser(): AuthUser
    {
        if (!$this->isLoggedIn()) {
            throw new NoUserLoggedIn();
        }

        return $this->userGateway->findUserByUsername(
            $this->session->getLoggedInUsername()
        );
    }

    public function hasAuthenticatedThisSession(): bool
    {
        return $this->session->userHasAuthenticatedThisSession();
    }
}
