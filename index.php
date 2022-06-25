<?php declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthenticationFailed;
use CEmerson\Auth\Providers\AwsCognito\AuthChallenges\MFARequired\MFARequiredChallenge;
use CEmerson\Auth\Providers\AwsCognito\AuthChallenges\NewPasswordRequired\NewPasswordRequiredChallenge;

$auth = require __DIR__ . "/auth.php";

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
        } else if ($response instanceof MFARequiredChallenge) {
            $challengeResponse = readline("Please enter MFA code: ");
        }

        $response = $auth->respondToChallenge($response->createChallengeResponse($challengeResponse));
    }

    if ($response instanceof AuthSucceededResponse) {
        echo "Authentication succeeded! You are logged in as " . $response->getIdToken() . PHP_EOL;
    }
} catch (AuthenticationFailed $ex) {
    print_r(get_class($ex->getAuthenticationFailedResponse()));
}
