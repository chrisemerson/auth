<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use CEmerson\Auth\UserProvider;

class AwsCognitoUserProvider implements UserProvider
{
    public function getLoggedInUserIdentifier(): string
    {
        return '';
    }

    public function getLoggedInUsername(): string
    {
        return '';
    }
}
