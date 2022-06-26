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
$debugMode = false;

$authContext = new class implements AuthContext
{
    private const SESSION_FILENAME = __DIR__ . "/session.json";
    private const REMEMBERED_LOGIN_FILENAME = __DIR__ . "/rememberedlogin.json";

    public function getSessionInfo(): array
    {
        return $this->getDataFromFile(self::SESSION_FILENAME);
    }

    public function saveSessionInfo(array $sessionInfo): void
    {
        $this->saveInfoToFile(self::SESSION_FILENAME, $sessionInfo);
    }

    public function deleteSessionInfo(): void
    {
        $this->deleteIfExists(self::SESSION_FILENAME);
    }

    public function getRememberedLoginInfo(): array
    {
        return $this->getDataFromFile(self::REMEMBERED_LOGIN_FILENAME);
    }

    public function saveRememberedLoginInfo(array $rememberedLoginInfo): void
    {
        $this->saveInfoToFile(self::REMEMBERED_LOGIN_FILENAME, $rememberedLoginInfo);
    }

    public function deleteRememberedLoginInfo(): void
    {
        $this->deleteIfExists(self::REMEMBERED_LOGIN_FILENAME);
    }

    private function saveInfoToFile(string $filename, array $data)
    {
        $json = [];

        if (file_exists($filename)) {
            $json = json_decode(file_get_contents($filename), true);
        }

        $json = array_merge($json, $data);

        file_put_contents($filename, json_encode($json));
    }

    private function deleteIfExists(string $filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    private function getDataFromFile(string $filename): array
    {
        if (file_exists($filename)) {
            return json_decode(file_get_contents($filename), true);
        } else {
            return [];
        }
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
            echo "[" . strtoupper($level) . "]: " . $this->replacePsr3ContextVars($message, $context) . PHP_EOL;

            if ($this->debug) {
                print_r($context);
                echo PHP_EOL . PHP_EOL;
            }
        }
    }

    private function replacePsr3ContextVars(string $message, array $context): string
    {
        return str_replace(
            array_map(
                fn ($token) => '{' . $token . '}',
                array_keys($context)
            ),
            array_map(
                fn($value) => $this->getDisplayString($value),
                array_values($context)
            ),
            $message
        );
    }

    private function getDisplayString(mixed $value): string
    {
        return (string) $value;
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
