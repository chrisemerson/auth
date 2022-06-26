<?php

declare(strict_types=1);

namespace CEmerson\Auth\Exceptions;

use CEmerson\Auth\AuthResponses\AuthFailedResponse;
use Throwable;

class AuthFailed extends AuthException
{
    private ?AuthFailedResponse $authenticationFailedResponse;

    public function __construct(
        ?AuthFailedResponse $authenticationFailedResponse,
        $message = "",
        $code = 0,
        Throwable $previous = null
    ) {
        $this->authenticationFailedResponse = $authenticationFailedResponse;

        parent::__construct($message, $code, $previous);
    }

    public function getAuthenticationFailedResponse(): ?AuthFailedResponse
    {
        return $this->authenticationFailedResponse;
    }
}
