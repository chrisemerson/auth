<?php

namespace CEmerson\Auth\Providers;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use Aws\Result;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallengeResponse;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationDetailsIncorrectResponse;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationResponse;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\PasswordResetRequired\PasswordResetRequiredChallenge;
use CEmerson\Auth\Providers\AuthenticationResponse\UserNotFoundResponse;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AwsCognitoAuthProvider implements AuthProvider
{
    private CognitoIdentityProviderClient $awsCognitoClient;
    private string $userPoolId;
    private string $clientId;
    private string $clientSecret;
    private LoggerInterface $logger;

    public function __construct(
        CognitoIdentityProviderClient $awsCognitoClient,
        string $userPoolId,
        string $clientId,
        string $clientSecret,
        LoggerInterface $logger = null
    ) {
        $this->awsCognitoClient = $awsCognitoClient;
        $this->userPoolId = $userPoolId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger ?? new NullLogger();
    }

    public function attemptAuthentication(AuthenticationParameters $authParameters): AuthenticationResponse
    {
        try {
            return $this->parseCognitoResponse(
                $this->awsCognitoClient->adminInitiateAuth([
                    'AuthFlow' => 'ADMIN_NO_SRP_AUTH',
                    'AuthParameters' => [
                        'USERNAME' => $authParameters->getUsername(),
                        'PASSWORD' => $authParameters->getPassword(),
                        'SECRET_HASH' => $this->hash($authParameters->getUsername() . $this->clientId)
                    ],
                    'ClientId' => $this->clientId,
                    'UserPoolId' => $this->userPoolId
                ])
            );
        } catch (CognitoIdentityProviderException $ex) {
            return $this->parseCognitoException($ex);
        }
    }

    public function respondToAuthenticationChallenge(
        AuthenticationChallengeResponse $authenticationChallengeResponse
    ): AuthenticationResponse {
        try {
            $challengeResponses = $authenticationChallengeResponse->getChallengeResponses();

            if ($authenticationChallengeResponse->isSecretHashRequired()) {
                $challengeResponses['SECRET_HASH'] = $this->hash(
                    $authenticationChallengeResponse->getUsername()
                    . $this->clientId
                );
            }

            return $this->parseCognitoResponse(
                $this->awsCognitoClient->respondToAuthChallenge([
                    'ChallengeName' => $authenticationChallengeResponse->getChallengeName(),
                    'Session' => $authenticationChallengeResponse->getChallengeId(),
                    'ChallengeResponses' => $challengeResponses,
                    'ClientId' => $this->clientId
                ])
            );
        } catch (CognitoIdentityProviderException $ex) {
            return $this->parseCognitoException($ex);
        }
    }

    private function parseCognitoResponse(Result $cognitoResponse): AuthenticationResponse
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
                    return new PasswordResetRequiredChallenge($cognitoResponse->get('Session'), $username);
            }
        }

        return new UserNotFoundResponse();
    }

    private function parseCognitoException(CognitoIdentityProviderException $ex): AuthenticationResponse
    {
        if ($ex->getResponse()->getStatusCode() === StatusCodeInterface::STATUS_BAD_REQUEST) {
            $ex->getResponse()->getBody()->rewind();
            $response = json_decode($ex->getResponse()->getBody()->getContents());

            print_r($response);


            switch ($response->__type) {
                case 'NotAuthorizedException':
                    return new AuthenticationDetailsIncorrectResponse();
            }
        }

        return new AuthenticationDetailsIncorrectResponse();
    }

    public function changePassword(string $username, string $oldPassword, string $newPassword): bool
    {

    }

    public function forgotPassword(string $username)
    {

    }

    public function registerUser(string $username, string $password)
    {

    }

    private function hash(string $string)
    {
        return base64_encode(
            hash_hmac(
                'sha256',
                $string,
                $this->clientSecret,
                true
            )
        );
    }
}