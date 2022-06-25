<?php

namespace CEmerson\Auth\Exceptions;

use CEmerson\Auth\AuthenticationResponse\AuthenticationFailedResponse;
use Throwable;

class AuthenticationFailed extends AuthException
{
    private ?AuthenticationFailedResponse $authenticationFailedResponse;

    public function __construct(
        ?AuthenticationFailedResponse $authenticationFailedResponse,
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->authenticationFailedResponse = $authenticationFailedResponse;

        parent::__construct($message, $code, $previous);
    }

    public function getAuthenticationFailedResponse(): ?AuthenticationFailedResponse
    {
        return $this->authenticationFailedResponse;
    }
}
