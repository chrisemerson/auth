<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Psr7AuthContext implements AuthContext
{
    public function deleteSessionInfo()
    {
        // TODO: Implement deleteSessionInfo() method.
    }

    public function deleteRememberedLogin()
    {
        // TODO: Implement deleteRememberedLogin() method.
    }

    public function populateContextFromPsr7Request(ServerRequestInterface $request)
    {

    }

    public function addContextValuesToResponse(ResponseInterface $response)
    {

    }
}
