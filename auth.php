<?php

declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use CEmerson\Auth\Auth;
use CEmerson\Auth\AuthContexts\AuthContext;
use CEmerson\Auth\Providers\AwsCognito\AwsCognitoAuthProvider;
use CEmerson\Auth\Providers\AwsCognito\AwsCognitoConfiguration;
use CEmerson\Auth\Providers\AwsCognito\AwsCognitoResponseParser;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

$config = require __DIR__ . "/config.php";
$debugMode = true;

$authContext = new class implements AuthContext
{
    public function deleteSessionInfo()
    {
    }

    public function deleteRememberedLogin()
    {
    }
};

$logger = new class($debugMode) extends AbstractLogger implements LoggerInterface {
    private bool $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function log($level, $message, array $context = array())
    {
        if ($this->debug || $level !== LogLevel::DEBUG) {
            echo "[" . strtoupper($level) . "]: " . $message . PHP_EOL;
            print_r($context);
            echo PHP_EOL . PHP_EOL;
        }
    }
};

$cognitoConfig = new AwsCognitoConfiguration(
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
    $config['client_secret']
);

$provider = new AwsCognitoAuthProvider(
    $cognitoConfig,
    new AwsCognitoResponseParser($logger),
    $logger
);

return new Auth($authContext, $logger, $provider);