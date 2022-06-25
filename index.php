<?php declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use CEmerson\Auth\Auth;
use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\Exceptions\AuthenticationFailed;
use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponse\AuthChallenge\AuthChallenge;
use CEmerson\Auth\AuthResponse\AuthSucceededResponse;
use CEmerson\Auth\Providers\AwsCognito\AuthChallenge\NewPasswordRequired\NewPasswordRequiredChallenge;
use CEmerson\Auth\Providers\AwsCognito\AwsCognitoAuthProvider;
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

$params = new AuthParameters($username, $password);

try {
    $response = $auth->attemptAuthentication($params);

    while ($response instanceof AuthChallenge) {
        echo "New password is required!" . PHP_EOL;
        if ($response instanceof NewPasswordRequiredChallenge) {
            $challengeResponse = readline("New password required: ");
        }

        $response = $auth->respondToChallenge($response->createChallengeResponse($challengeResponse));
    }

    if ($response instanceof AuthSucceededResponse) {
        echo "Authentication succeeded! You are logged in as " . $response->getIdToken() . PHP_EOL;
    }
} catch (AuthenticationFailed $ex) {
    print_r(get_class($ex->getAuthenticationFailedResponse()));
}
