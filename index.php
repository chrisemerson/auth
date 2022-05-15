<?php

require_once __DIR__ . "/vendor/autoload.php";

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use CEmerson\Auth\Auth;
use CEmerson\Auth\AuthContexts\NativePhpAuthContext;
use CEmerson\Auth\Providers\AuthenticationParameters;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallenge;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\PasswordResetRequired\PasswordResetRequiredChallenge;
use CEmerson\Auth\Providers\AwsCognitoAuthProvider;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

$authContext = new NativePHPAuthContext();

$logger = new class extends AbstractLogger implements LoggerInterface {
    public function log($level, $message, array $context = array())
    {
        echo "[" . strtoupper($level) . "]: " . $message . PHP_EOL;
        print_r($context);
        echo PHP_EOL . PHP_EOL;
    }
};

$provider = new AwsCognitoAuthProvider(
    new CognitoIdentityProviderClient([
        'region' => 'eu-west-1',
        'version' => '2016-04-18',
        'credentials' => [
            'key' => 'AKIA56GU4R4DPU2PO3ZH',
            'secret' => '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'
        ]
    ]),
    'eu-west-1_LwVC9jq69',
    '2vhihdf1q4jkt52bpvlqtsvvv6',
    'q2c5bah196adk27o1oj2b5771bqc73as9asa050bvehrapap51n',
    $logger
);

$auth = new Auth($authContext, $logger, $provider);

$username = readline("Username: ");
$password = readline("Password: ");

$params = new AuthenticationParameters($username, $password);

try {
    $response = $auth->attemptAuthentication($params);

    while ($response instanceof AuthenticationChallenge) {
        if ($response instanceof PasswordResetRequiredChallenge) {
            $challengeResponse = readline("New password required: ");
        }

        $response = $auth->respondToChallenge($response->createChallengeResponse($challengeResponse));
    }

    print_r($response);
} catch (\CEmerson\Auth\Exceptions\AuthenticationFailed $ex) {
    print_r(get_class($ex->getAuthenticationFailedResponse()));
}
