<?php declare(strict_types = 1);

namespace CEmerson\Auth\AuthContexts\Session;

interface SessionGateway
{
    public function start();

    public function read(string $name);

    public function write(string $name, $data);

    public function exists(string $name): bool;

    public function regenerate();

    public function delete(string $name);
}
