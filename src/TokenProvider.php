<?php

namespace CEmerson\Auth;

interface TokenProvider
{
    public function getAccessToken(): string;

    public function getIdToken(): string;

    public function getRefreshToken(): string;
}
