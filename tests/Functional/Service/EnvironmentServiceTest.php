<?php

namespace App\Tests\Functional\Service;


use App\Service\EnvironmentService;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Tests of EnvironmentService')]
class EnvironmentServiceTest extends TestCase
{

    #[TestDox('load sets secret environment variables')]
    public function testLoadSetsSecretEnvVars(): void
    {
        EnvironmentService::load();

        $this->assertNotEmpty($_ENV['APP_SECRET']);
        $this->assertNotEmpty($_ENV['DATABASE_URL']);
        $this->assertNotEmpty($_ENV['MARIADB_PASSWORD']);
    }
}
