<?php

namespace CEmerson\Auth;

interface UserProvider
{
    public function getLoggedInUserIdentifier(): string;

    public function getLoggedInUsername(): string;
}
