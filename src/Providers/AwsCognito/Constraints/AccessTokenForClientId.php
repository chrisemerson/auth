<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\Constraints;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;

class AccessTokenForClientId implements Constraint
{
    private string $clientId;

    public function __construct(string $clientId)
    {
        $this->clientId = $clientId;
    }

    public function assert(Token $token): void
    {
        if (
            !(
                $token instanceof UnencryptedToken
                && $this->clientId === $token->claims()->get('client_id', null)
            )
        ) {
            throw new ConstraintViolation(
                'The token is not configured for this app client'
            );
        }
    }
}
