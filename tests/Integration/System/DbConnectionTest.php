<?php

namespace App\Tests\Integration\System;

use App\Tests\Integration\Fixtures\Database as DB;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[TestDox('Tests Database objects')]
class DbConnectionTest extends KernelTestCase
{
    #[TestDox('It can connect using the raw DB connection values')]
    public function testPdoCanConnectToTheDatabase(): void
    {
        $connection = DB::getPdoConnection();

        $result = $connection->query('SELECT 1 AS col')->fetch();

        $this->assertEquals(1, $result['col']);
    }

    #[TestDox('It can use DATABASE_URL to connect to the database')]
    public function testDoctrineCanConnectToTheDatabase(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $connection = $container->get('doctrine')->getConnection('default');

        $result = $connection->executeQuery('SELECT 1')->fetchOne();

        $this->assertEquals(1, $result);
    }
}
