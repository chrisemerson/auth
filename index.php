<?php declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use CEmerson\Auth\Auth;
use CEmerson\Auth\AuthParameters;
use CEmerson\Auth\AuthResponses\AuthChallenges\AuthChallenge;
use CEmerson\Auth\AuthResponses\AuthSucceededResponse;
use CEmerson\Auth\Exceptions\AuthFailed;

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

$command = '';

while (strtolower($command) !== "e") {
    if ($auth->isLoggedIn()) {
        echo "Logged in as " . $auth->getCurrentUser()->get('username') . PHP_EOL;

        $command = readline("[l]ogout, [c]hange password, [s]etup MFA, [e]xit?: ");

        switch (strtolower($command)) {
            case 'l':
                $auth->logout();;
                echo "Logged out" . PHP_EOL;
                break;

            case 'c':
                break;

            case 's':
                break;
        }
    } else {
        echo "Not logged in" . PHP_EOL;

        $command = readline("[l]ogin, [f]orgot password, [e]xit?: ");

        switch (strtolower($command)) {
            case 'l':
                $username = readline("Username: ");
                $password = readline("Password: ");

                $params = new AuthParameters($username, $password, true);

                try {
                    $response = $auth->attemptAuthentication($params);

                    while ($response instanceof AuthChallenge) {
                        $challengeResponse = readline("Challenge - " . basename(get_class($response)) . ": ");

                        $response = $auth->respondToChallenge($response->createChallengeResponse($challengeResponse));
                    }

                    if ($response instanceof AuthSucceededResponse) {
                        echo "Authentication succeeded! You are logged in as " . $auth->getCurrentUsername() . PHP_EOL;
                    }
                } catch (AuthFailed $ex) {
                    print_r(get_class($ex->getAuthenticationFailedResponse()));
                }
                break;

            case 'f':
                break;
        }
    }
}
