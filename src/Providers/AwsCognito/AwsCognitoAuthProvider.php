<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthProvider;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponses\AuthResponse;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AwsCognitoAuthProvider implements AuthProvider
{
    private AwsCognitoConfiguration $awsCognitoConfiguration;
    private AwsCognitoResponseParser $awsCognitoResponseParser;
    private LoggerInterface $logger;

    public function __construct(
        AwsCognitoConfiguration $awsCognitoConfiguration,
        AwsCognitoResponseParser $awsCognitoResponseParser,
        LoggerInterface $logger = null
    ) {
        $this->awsCognitoConfiguration = $awsCognitoConfiguration;
        $this->awsCognitoResponseParser = $awsCognitoResponseParser;
        $this->logger = $logger ?? new NullLogger();
    }

    public function attemptAuthentication(AuthParameters $authParameters): AuthResponse
    {
        try {
            return $this->awsCognitoResponseParser->parseCognitoResponse(
                $this->awsCognitoConfiguration->getAwsCognitoClient()->adminInitiateAuth([
                    'AuthFlow' => 'ADMIN_NO_SRP_AUTH',
                    'AuthParameters' => [
                        'USERNAME' => $authParameters->getUsername(),
                        'PASSWORD' => $authParameters->getPassword(),
                        'SECRET_HASH' => $this->awsCognitoConfiguration->hash(
                            $authParameters->getUsername() . $this->awsCognitoConfiguration->getClientId(),
                        )
                    ],
                    'ClientId' => $this->awsCognitoConfiguration->getClientId(),
                    'UserPoolId' => $this->awsCognitoConfiguration->getUserPoolId()
                ])
            );
        } catch (CognitoIdentityProviderException $ex) {
            return $this->awsCognitoResponseParser->parseCognitoException($ex);
        }
    }

    public function respondToAuthenticationChallenge(
        AuthChallengeResponse $authenticationChallengeResponse
    ): AuthResponse {
        try {
            $challengeResponses = $authenticationChallengeResponse->getChallengeParameters();

            if ($authenticationChallengeResponse->isSecretHashRequired()) {
                $challengeResponses['SECRET_HASH'] = $this->awsCognitoConfiguration->hash(
                    $authenticationChallengeResponse->getUsername()
                    . $this->awsCognitoConfiguration->getClientId(),
                );
            }

            return $this->awsCognitoResponseParser->parseCognitoResponse(
                $this->awsCognitoClient->respondToAuthChallenge([
                    'ChallengeName' => $authenticationChallengeResponse->getChallengeName(),
                    'Session' => $authenticationChallengeResponse->getChallengeId(),
                    'ChallengeResponses' => $challengeResponses,
                    'ClientId' => $this->awsCognitoConfiguration->getClientId()
                ])
            );
        } catch (CognitoIdentityProviderException $ex) {
            return $this->awsCognitoResponseParser->parseCognitoException($ex);
        }
    }

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool
    {
        return false;
    }

    public function forgotPassword(string $username)
    {
    }

    public function registerUser(string $username, string $password)
    {
    }

    public function logout()
    {
        // TODO: Implement logout() method.
    }
}
