<?php declare(strict_types = 1);

namespace CEmerson\Auth;

use CEmerson\Auth\Exceptions\NoUserLoggedIn;
use CEmerson\Auth\Exceptions\UserAlreadyLoggedIn;
use CEmerson\Auth\Exceptions\UserNotFound;
use CEmerson\Auth\RememberedLogins\RememberedLoginService;
use CEmerson\Auth\Session\Session;
use CEmerson\Auth\Users\AuthUser;
use CEmerson\Auth\Users\AuthUserGateway;
use CEmerson\Auth\Users\WriteBackAuthUserGateway;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class Auth implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var AuthUserGateway */
    private $userGateway;

    /** @var RememberedLoginService */
    private $rememberedLoginService;

    /** @var Session */
    private $session;

    /** @var WriteBackAuthUserGateway */
    private $writeBackAuthUserGateway = null;

    public function __construct(
        AuthUserGateway $userGateway,
        Session $session,
        RememberedLoginService $rememberedLoginService
    ) {
        $this->userGateway = $userGateway;
        $this->rememberedLoginService = $rememberedLoginService;
        $this->session = $session;

        $this->setLogger(new NullLogger());
    }

    public function setWriteBackAuthUserGateway(WriteBackAuthUserGateway $writeBackAuthUserGateway)
    {
        $this->writeBackAuthUserGateway = $writeBackAuthUserGateway;
    }

    public function login(string $username, string $password, bool $rememberMe = false): bool
    {
        if ($this->isLoggedIn()) {
            throw new UserAlreadyLoggedIn();
        }

        try {
            $user = $this->userGateway->findUserByUsername($username);
        } catch (UserNotFound $e) {
            return false;
        }

        return $this->handleUserAuthentication($user, $password, $rememberMe);
    }

    private function handleUserAuthentication(AuthUser $user, string $password, bool $rememberMe): bool
    {
        if ($this->verifyPassword($user, $password)) {
            $this->session->onSuccessfulAuthentication($user);
            $this->writeBackUser($user, $password);

            if ($rememberMe) {
                $this->rememberedLoginService->rememberLogin($user);
            }

            return true;
        }

        return false;
    }

    private function verifyPassword(AuthUser $user, string $password): bool
    {
        $passwordHashingStrategy = $user->getPasswordHashingStrategy();
        $passwordHash = $user->getPasswordHash();

        return $passwordHashingStrategy->verifyPassword($password, $passwordHash);
    }

    public function logout()
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        $this->session->deleteAuthSessionInfo();
        $this->rememberedLoginService->deleteRememberedLogin();
    }

    public function isLoggedIn(): bool
    {
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

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
        $this->rememberedLoginService->loadRememberedLoginFromCookie();

        return $this->session->userHasAuthenticatedThisSession();
    }

    public function reAuthenticateCurrentUser(string $password): bool
    {
        $currentUser = $this->getCurrentUser();

        return $this->handleUserAuthentication($currentUser, $password, true);
    }

    private function writeBackUser(AuthUser $user, string $password)
    {
        if (!is_null($this->writeBackAuthUserGateway)) {
            $passwordHashingStrategy = $this->writeBackAuthUserGateway->getPasswordHashingStrategy();
            $newPasswordHash = $passwordHashingStrategy->hashPassword($password);
            $this->writeBackAuthUserGateway->saveUser($user, $newPasswordHash);
        }
    }

    public function cleanup()
    {
        $this->rememberedLoginService->cleanupExpiredRememberedLogins();
    }
}
