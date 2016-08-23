<?php declare(strict_types = 1);

namespace CEmerson\AceAuth\Session;

interface SessionGateway
{
    public function read(string $name);
    public function write(string $name, $data);
}