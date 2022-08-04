<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthProvider;
use CEmerson\Auth\AuthResponse;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AwsCognitoAuthProvider implements AuthProvider
{
    private AwsCognitoConfiguration $awsCognitoConfiguration;
    private AwsCognitoResponseParser $awsCognitoResponseParser;
    private AwsCognitoJwtTokenValidator $tokenValidator;
    private LoggerInterface $logger;

    public function __construct(
        AwsCognitoConfiguration $awsCognitoConfiguration,
        AwsCognitoResponseParser $awsCognitoResponseParser,
        AwsCognitoJwtTokenValidator $tokenValidator,
        LoggerInterface $logger = null
    ) {
        $this->awsCognitoConfiguration = $awsCognitoConfiguration;
        $this->awsCognitoResponseParser = $awsCognitoResponseParser;
        $this->tokenValidator = $tokenValidator;
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

    public function attemptAuthenticationWithRefreshToken(string $refreshToken, string $username): AuthResponse
    {
        try {
            return $this->awsCognitoResponseParser->parseCognitoResponse(
                $this->awsCognitoConfiguration->getAwsCognitoClient()->adminInitiateAuth([
                    'AuthFlow' => 'REFRESH_TOKEN_AUTH',
                    'AuthParameters' => [
                        'REFRESH_TOKEN' => $refreshToken,
                        'SECRET_HASH' => $this->awsCognitoConfiguration->hash(
                            $username . $this->awsCognitoConfiguration->getClientId(),
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
    }

    public function isSessionValid(array $sessionInfo): bool
    {
        return
            isset($sessionInfo[AwsCognitoAuthSucceededResponse::ACCESS_TOKEN_KEY_NAME])
                && $sessionInfo[AwsCognitoAuthSucceededResponse::ACCESS_TOKEN_KEY_NAME] !== null
                && $this->tokenValidator->validateToken(
                    $sessionInfo[AwsCognitoAuthSucceededResponse::ACCESS_TOKEN_KEY_NAME],
                    'access'
                )
                && isset($sessionInfo[AwsCognitoAuthSucceededResponse::ID_TOKEN_KEY_NAME])
                && $sessionInfo[AwsCognitoAuthSucceededResponse::ID_TOKEN_KEY_NAME] !== null
                && $this->tokenValidator->validateToken(
                    $sessionInfo[AwsCognitoAuthSucceededResponse::ID_TOKEN_KEY_NAME],
                    'id'
                );
    }

    public function refreshSessionTokens(array $sessionInfo, array $rememberedLoginInfo): array
    {
        $refreshToken = null;
        $username = null;

        if (
            isset($sessionInfo[AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME])
            && !is_null($sessionInfo[AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME])
            && isset($sessionInfo[AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME])
            && !is_null($sessionInfo[AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME])
        ) {
            $refreshToken = $sessionInfo[AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME];
            $username = $sessionInfo[AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME];
        } elseif (
            isset($rememberedLoginInfo[AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME])
            && !is_null($rememberedLoginInfo[AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME])
            && isset($rememberedLoginInfo[AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME])
            && !is_null($rememberedLoginInfo[AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME])
        ) {
            $refreshToken = $rememberedLoginInfo[AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME];
            $username = $rememberedLoginInfo[AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME];
        }

        if (!is_null($refreshToken) && !is_null($username)) {
            $response = $this->attemptAuthenticationWithRefreshToken($refreshToken, $username);

            if ($response instanceof AuthSucceededResponse) {
                return array_merge(
                    $response->getSessionInfo(),
                    [
                        AwsCognitoAuthSucceededResponse::USERNAME_KEY_NAME => $username,
                        AwsCognitoAuthSucceededResponse::REFRESH_TOKEN_KEY_NAME => $refreshToken
                    ]
                );
            }
        }

        return [];
    }
}
