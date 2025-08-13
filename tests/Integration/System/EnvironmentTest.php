<?php

namespace App\Tests\Integration\System;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Tests of environment variables')]
class EnvironmentTest extends TestCase
{
    #[TestDox('The expected environment variables exist')]
    public function testEnvironmentVariables(): void
    {
        $varNames = [
            'APP_CACHE_DIR',
            'APP_LOG_DIR',
            'MARIADB_DATABASE',
            'MARIADB_HOST',
            'MARIADB_PORT',
            'MARIADB_USER',
        ];

        foreach ($varNames as $varName) {
            $this->assertNotFalse(
                getenv($varName),
                "Expected environment variable $varName to exist"
            );
        }
    }

    #[TestDox('Prohibited environment variables are not set')]
    public function testProhibitedEnvironmentVariables(): void
    {
        $varNames = [
            'APP_SECRET',
            'DATABASE_URL',
            'MARIADB_PASSWORD',
        ];

        foreach ($varNames as $varName) {
            $this->assertFalse(
                getenv($varName),
                "Prohibited environment variable $varName should not be set"
            );
        }
    }

    #[TestDox('Secret environment variables are set')]
    public function testSecretEnvironmentVariables(): void
    {
        $varNames = [
            'APP_SECRET',
            'DATABASE_URL',
            'MARIADB_PASSWORD',
        ];

        foreach ($varNames as $varName) {
            $this->assertArrayHasKey(
                $varName,
                $_ENV,
                "Expected secret environment variable $varName to be set"
            );
        }
    }
}
