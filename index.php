<?php

require_once __DIR__ . "/vendor/autoload.php";

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use CEmerson\Auth\Auth;
use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\Exceptions\AuthenticationFailed;
use CEmerson\Auth\Providers\AuthenticationParameters;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\AuthenticationChallenge;
use CEmerson\Auth\Providers\AuthenticationResponse\AuthenticationChallenge\NewPasswordRequired\NewPasswordRequiredChallenge;
use CEmerson\Auth\Providers\AwsCognitoAuthProvider;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

$config = require __DIR__ . "/config.php";

$authContext = new class implements AuthContext
{

};

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
            'key' => $config['access_key'],
            'secret' => $config['secret_key'],
        ]
    ]),
    $config['user_pool_id'],
    $config['client_id'],
    $config['client_secret'],
    $logger
);

$auth = new Auth($authContext, $logger, $provider);

/* Auth possibilities:

 - Username / password incorrect
 - MFA required
 - MFA incorrect
 - New password required
 - Too many tries / locked out

 - Remembered login (save in file)
 - If file exists, authenticate with it */

$username = readline("Username: ");
$password = readline("Password: ");

$params = new AuthenticationParameters($username, $password);

try {
    $response = $auth->attemptAuthentication($params);

    while ($response instanceof AuthenticationChallenge) {
        echo "New password is required!" . PHP_EOL;
        if ($response instanceof NewPasswordRequiredChallenge) {
            $challengeResponse = readline("New password required: ");
        }

        $response = $auth->respondToChallenge($response->createChallengeResponse($challengeResponse));
    }

    print_r($response);
} catch (AuthenticationFailed $ex) {
    print_r(get_class($ex->getAuthenticationFailedResponse()));
}