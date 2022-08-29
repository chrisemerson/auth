<?php

declare(strict_types=1);

namespace CEmerson\Auth\Providers\AwsCognito;

use Aws\CognitoIdentityProvider\Exception\CognitoIdentityProviderException;
use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthProvider;
use CEmerson\Auth\AuthResponse;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallengeResponse;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthException;
use CEmerson\Auth\Exceptions\InvalidPassword;
use CEmerson\Auth\Exceptions\RateLimitExceeded;
use CEmerson\Auth\Exceptions\VerificationCodeExpired;
use CEmerson\Auth\User\DefaultAuthUser;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AwsCognitoAuthProvider implements AuthProvider
{
    private AwsCognitoConfiguration $awsCognitoConfiguration;
    private AwsCognitoResponseParser $awsCognitoResponseParser;
    private AwsCognitoJwtTokenValidator $tokenValidator;
    private AwsCognitoAuthChallengeResponseFactory $authChallengeResponseFactory;
    private LoggerInterface $logger;

    public function __construct(
        AwsCognitoConfiguration $awsCognitoConfiguration,
        AwsCognitoResponseParser $awsCognitoResponseParser,
        AwsCognitoJwtTokenValidator $tokenValidator,
        AwsCognitoAuthChallengeResponseFactory $authChallengeResponseFactory,
        LoggerInterface $logger = null
    ) {
        $this->awsCognitoConfiguration = $awsCognitoConfiguration;
        $this->awsCognitoResponseParser = $awsCognitoResponseParser;
        $this->tokenValidator = $tokenValidator;
        $this->authChallengeResponseFactory = $authChallengeResponseFactory;
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
        string $authenticationChallengeName,
        string $authenticationChallengeDetails,
        string $authenticationChallengeResponse
    ): AuthResponse {
        $challengeResponse = $this->authChallengeResponseFactory->createAuthenticationResponse(
            $authenticationChallengeName,
            $authenticationChallengeDetails,
            $authenticationChallengeResponse
        );

        return $this->sendAuthChallengeResponse($challengeResponse);
    }

    private function sendAuthChallengeResponse(AuthChallengeResponse $authenticationChallengeResponse): AuthResponse
    {
        try {
            $challengeResponses = $authenticationChallengeResponse->getChallengeParameters();

            if ($authenticationChallengeResponse->isSecretHashRequired()) {
                $challengeResponses['SECRET_HASH'] = $this->awsCognitoConfiguration->hash(
                    $authenticationChallengeResponse->getUsername()
                    . $this->awsCognitoConfiguration->getClientId(),
                );
            }

            return $this->awsCognitoResponseParser->parseCognitoResponse(
                $this->awsCognitoConfiguration->getAwsCognitoClient()->respondToAuthChallenge([
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

    public function changePassword(array $sessionInfo, string $oldPassword, string $newPassword): bool
    {
        $response = $this->awsCognitoConfiguration->getAwsCognitoClient()->changePassword([
            'AccessToken' => $sessionInfo[AwsCognitoAuthSucceededResponse::ACCESS_TOKEN_KEY_NAME],
            'PreviousPassword' => $oldPassword,
            'ProposedPassword' => $newPassword
        ]);

        print_r($response);
        exit();

        return false;
    }

    public function forgotPassword(string $username)
    {
        try {
            $response = $this->awsCognitoConfiguration->getAwsCognitoClient()->adminResetUserPassword([
                'UserPoolId' => $this->awsCognitoConfiguration->getUserPoolId(),
                'Username' => $username
            ]);

            if ($response->get('@metadata')['statusCode'] !== 200) {
                throw new AuthException();
            }
        } catch (CognitoIdentityProviderException $ex) {
            throw $ex;
        }
    }

    public function resetForgottenPassword(string $username, string $confirmationCode, string $newPassword)
    {
        try {
            $this->awsCognitoConfiguration->getAwsCognitoClient()->confirmForgotPassword([
                'ClientId' => $this->awsCognitoConfiguration->getClientId(),
                'ConfirmationCode' => $confirmationCode,
                'Password' => $newPassword,
                'SecretHash' => $this->awsCognitoConfiguration->hash(
                    $username
                    . $this->awsCognitoConfiguration->getClientId()
                ),
                'Username' => $username
            ]);
        } catch (CognitoIdentityProviderException $ex) {
            switch ($ex->getAwsErrorCode()) {
                case 'LimitExceededException':
                    throw new RateLimitExceeded($ex->getMessage(), $ex->getCode(), $ex);

                case 'ExpiredCodeException':
                    throw new VerificationCodeExpired($ex->getMessage(), $ex->getCode(), $ex);

                case 'InvalidPasswordException':
                    throw new InvalidPassword($ex->getMessage(), $ex->getCode(), $ex);

                default:
                    throw new AuthException();
            }
        }
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

    public function getCurrentUser(array $sessionInfo): DefaultAuthUser
    {
        $unencryptedToken = (new Parser(new JoseEncoder()))->parse(
            $sessionInfo[AwsCognitoAuthSucceededResponse::ID_TOKEN_KEY_NAME]
        );

        $attributes = $unencryptedToken->claims()->all();

        $attributesToUnset = [
            'aud',
            'auth_time',
            'cognito:username',
            'event_id',
            'exp',
            'iat',
            'iss',
            'jti',
            'origin_jti',
            'sub',
            'token_use'
        ];

        $attributes['username'] = $attributes['cognito:username'];
        $attributes['avatar'] = $attributes['picture'];

        foreach ($attributesToUnset as $attributeToUnset) {
            unset($attributes[$attributeToUnset]);
        }

        return new DefaultAuthUser(
            $unencryptedToken->claims()->get('sub'),
            $attributes
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
