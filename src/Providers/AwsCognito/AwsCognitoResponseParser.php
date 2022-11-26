<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Result;
use CEmerson\Auth\AuthResponse;
use CEmerson\Auth\AuthResponses\AuthDetailsIncorrectResponse;
use CEmerson\Auth\AuthResponses\PasswordDoesNotConformToPolicyResponse;
use CEmerson\Auth\AuthResponses\RateLimitExceededResponse;
use CEmerson\Auth\AuthResponses\TokenValidationError;
use CEmerson\Auth\AuthResponses\UserNotFoundResponse;
use CEmerson\Auth\Providers\AwsCognito\AuthChallenges\NewPasswordRequired\NewPasswordRequiredChallenge;
use Fig\Http\Message\StatusCodeInterface;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Psr\Log\LoggerInterface;

class AwsCognitoResponseParser
{
    private LoggerInterface $logger;
    private AwsCognitoJwtTokenValidator $validator;

    public function __construct(LoggerInterface $logger, AwsCognitoJwtTokenValidator $validator)
    {
        $this->logger = $logger;
        $this->validator = $validator;
    }

    public function parseCognitoResponse(Result $cognitoResponse): AuthResponse
    {
        $this->logger->debug('Cognito Response', [
            'response' => $cognitoResponse
        ]);

        if ($cognitoResponse->hasKey('ChallengeName')) {
            // Other possible values:

            // SMS_MFA
            // SOFTWARE_TOKEN_MFA
            // SELECT_MFA_TYPE
            // MFA_SETUP
            // PASSWORD_VERIFIER
            // CUSTOM_CHALLENGE
            // DEVICE_SRP_AUTH
            // DEVICE_PASSWORD_VERIFIER
            // ADMIN_NO_SRP_AUTH

            switch ($cognitoResponse->get('ChallengeName')) {
                case 'NEW_PASSWORD_REQUIRED':
                    $username = $cognitoResponse->get('ChallengeParameters')['USER_ID_FOR_SRP'];
                    return new NewPasswordRequiredChallenge($cognitoResponse->get('Session'), $username);
            }
        }

        if ($cognitoResponse->hasKey('AuthenticationResult')) {
            $authenticationResult = $cognitoResponse->get('AuthenticationResult');

            if (
                isset($authenticationResult['AccessToken'])
                && isset($authenticationResult['IdToken'])
                && $this->validator->validateToken($authenticationResult['AccessToken'], 'access')
                && $this->validator->validateToken($authenticationResult['IdToken'], 'id')
            ) {
                return new AwsCognitoAuthSucceededResponse(
                    (new Parser(new JoseEncoder()))
                        ->parse($authenticationResult['IdToken'])
                        ->claims()
                        ->get('cognito:username'),
                    $authenticationResult['AccessToken'],
                    $authenticationResult['IdToken'],
                    $authenticationResult['RefreshToken'] ?? null
                );
            } else {
                return new TokenValidationError();
            }
        }

        return new UserNotFoundResponse();
    }

    public function parseCognitoException(CognitoIdentityProviderException $ex): AuthResponse
    {
        if ($ex->getResponse()->getStatusCode() === StatusCodeInterface::STATUS_BAD_REQUEST) {
            $ex->getResponse()->getBody()->rewind();
            $response = json_decode($ex->getResponse()->getBody()->getContents());

            $this->logger->debug("Exception contents", [
                'exception' => $response
            ]);

            switch ($response->__type) {
                case 'NotAuthorizedException':
                    return new AuthDetailsIncorrectResponse();

                case 'InvalidPasswordException':
                    return new PasswordDoesNotConformToPolicyResponse();

                case 'LimitExceededException':
                    return new RateLimitExceededResponse();
            }
        }

        return new AuthDetailsIncorrectResponse();
    }
}
