<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

class PHPSessionGateway implements SessionGateway
{
    public function start()
    {
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
}
