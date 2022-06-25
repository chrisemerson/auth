<?php

namespace CEmerson\Auth\Providers\Aws;

use CEmerson\Auth\TokenProvider;
use CEmerson\Auth\UserProvider;

class AwsCognitoUserProvider implements UserProvider
{
    private $tokenProvider;

    public function __construct (TokenProvider $tokenProvider)
    {
        $this->tokenProvider = $tokenProvider;
    }

    public function getLoggedInUserIdentifier(): string
    {
        return '';
    }

    public function getLoggedInUsername(): string
    {
        return '';
    }
}
