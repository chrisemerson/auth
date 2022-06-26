<?php declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use CEmerson\Auth\Auth;
use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthenticationFailed;

/** @var Auth $auth */
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
        $challengeResponse = readline("Challenge - " . basename(get_class($response)) . ": ");

        $response = $auth->respondToChallenge($response->createChallengeResponse($challengeResponse));
    }

    if ($response instanceof AuthSucceededResponse) {
        echo "Authentication succeeded! You are logged in as " . $auth->getCurrentUser() . PHP_EOL;
    }
} catch (AuthenticationFailed $ex) {
    print_r(get_class($ex->getAuthenticationFailedResponse()));
}
