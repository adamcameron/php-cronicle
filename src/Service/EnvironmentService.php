<?php

namespace App\Service;

use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

class EnvironmentService
{

    private const string APP_SECRET_FILE = '/run/secrets/app_secrets';

    public static function load(): void
    {
        if (!file_exists(EnvironmentService::APP_SECRET_FILE)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                'App secrets file not found: ' . EnvironmentService::APP_SECRET_FILE
            );
            // @codeCoverageIgnoreEnd
        }

        $dotEnv = new Dotenv();
        $dotEnv->loadEnv(EnvironmentService::APP_SECRET_FILE);
    }
}
