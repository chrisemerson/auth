<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use CEmerson\Auth\Providers\AwsCognito\Adapters\LcobucciClockAdapter;
use CEmerson\Auth\Providers\AwsCognito\Constraints\AccessTokenForClientId;
use CEmerson\Auth\Providers\AwsCognito\Constraints\MatchesExpectedUsage;
use CoderCat\JWKToPEM\JWKConverter;
use DateInterval;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;

class AwsCognitoJwtTokenValidator
{
    private AwsCognitoConfiguration $awsCognitoConfiguration;
    private ClockInterface $clock;
    private LoggerInterface $logger;

    public function __construct(AwsCognitoConfiguration $awsCognitoConfiguration, ClockInterface $clock, LoggerInterface $logger)
    {
        $this->awsCognitoConfiguration = $awsCognitoConfiguration;
        $this->clock = $clock;
        $this->logger = $logger;
    }

    public function validateToken(string $token, string $expectedUsage): bool
    {
        $tokenParser = new Parser(new JoseEncoder());
        $unencryptedToken = $tokenParser->parse($token);

        $jwkConverter = new JWKConverter();
        $jwkSet = json_decode($this->awsCognitoConfiguration->getJsonWebKeySet(), true);

        $pemKeys = [];

        foreach ($jwkSet['keys'] as $jwk) {
            $pemKeys[$jwk['kid']] = $jwkConverter->toPEM($jwk);
        }

        $keyInUse = $pemKeys[$unencryptedToken->headers()->get('kid')];

        $constraints = [
            new SignedWith(new Sha256(), InMemory::plainText($keyInUse)), // Check that it has been correctly signed by AWS
            new LooseValidAt(new LcobucciClockAdapter($this->clock), new DateInterval('PT5S')), // Check token has not expired
            new IssuedBy($this->awsCognitoConfiguration->getUserPoolUri()), // Check that the token has come from our user pool
            new MatchesExpectedUsage($expectedUsage)
        ];

        if ($expectedUsage === 'id') {
            $constraints[] = new PermittedFor($this->awsCognitoConfiguration->getClientId());
        } elseif ($expectedUsage === 'access') {
            $constraints[] = new AccessTokenForClientId($this->awsCognitoConfiguration->getClientId());
        }

        $validator = new Validator();

        try {
            $validator->assert($unencryptedToken, ...$constraints);

            return true;
        } catch (RequiredConstraintsViolated $e) {
            $this->logger->error("Error in taken validation - " . $e->getMessage());

            return false;
        }
    }
}
