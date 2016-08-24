<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

final class PHPSessionGateway implements SessionGateway
{
    public function start()
    {
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.entropy_length', 32);
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.hash_function', 'sha256');
        ini_set('session.hash_bits_per_character', 5);

        session_set_cookie_params(
            0,
            '/',
            null,
            true,
            true
        );

        session_start();
    }

    public function read(string $name)
    {
        return $_SESSION[$name];
    }

    public function write(string $name, $data)
    {
        $_SESSION[$name] = $data;
    }

    public function exists(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    public function regenerate()
    {
        session_regenerate_id(true);
    }

    public function destroy()
    {
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }
}
