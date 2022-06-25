<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Result;
use CEmerson\Auth\AuthResponses\AuthDetailsIncorrectResponse;
use CEmerson\Auth\AuthResponses\AuthResponse;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\AuthResponses\UserNotFoundResponse;
use CEmerson\Auth\Providers\AwsCognito\AuthChallenges\NewPasswordRequired\NewPasswordRequiredChallenge;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Log\LoggerInterface;

class AwsCognitoResponseParser
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
                && isset($authenticationResult['RefreshToken'])
            ) {
                try {
                    //Validate tokens here
                    return new AuthSucceededResponse(
                        $authenticationResult['AccessToken'],
                        $authenticationResult['IdToken'],
                        $authenticationResult['RefreshToken']
                    );
                } catch (Exception $e) {
                    //return something here, gone wrong
                }
            }
        }

        return new UserNotFoundResponse();
    }

    public function parseCognitoException(CognitoIdentityProviderException $ex): AuthResponse
    {
        if ($ex->getResponse()->getStatusCode() === StatusCodeInterface::STATUS_BAD_REQUEST) {
            $ex->getResponse()->getBody()->rewind();
            $response = json_decode($ex->getResponse()->getBody()->getContents());

            print_r($response);


            switch ($response->__type) {
                case 'NotAuthorizedException':
                    return new AuthDetailsIncorrectResponse();
            }
        }

        return new AuthDetailsIncorrectResponse();
    }
}
