<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito\Constraints;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;

class MatchesExpectedUsage implements Constraint
{
    private string $expectedUsage;

    public function __construct(string $expectedUsage)
    {
        $this->expectedUsage = $expectedUsage;
    }

    public function assert(Token $token): void
    {
        if (
            !(
                $token instanceof UnencryptedToken
                && $this->expectedUsage === $token->claims()->get('token_use', null)
            )
        ) {
            throw new ConstraintViolation(
                'The token is not intended for this use'
            );
        }
    }
}
