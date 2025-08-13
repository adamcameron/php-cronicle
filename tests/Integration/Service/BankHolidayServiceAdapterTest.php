<?php

namespace App\Tests\Integration\Service;

use App\Service\BankHolidayServiceAdapter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BankHolidayServiceAdapterTest extends KernelTestCase
{
    public function testFetchHolidaysFromConfiguredService(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $adapter = $container->get('testing.BankHolidayServiceAdapter');
        $this->assertInstanceOf(BankHolidayServiceAdapter::class, $adapter);

        $holidays = $adapter->fetchHolidays();

        $this->assertIsArray($holidays);
        $this->assertNotEmpty($holidays);

        $currentYear = date('Y');
        $expectedChristmasDate = $currentYear . '-12-25';

        $christmasHolidays = array_filter($holidays, function ($holiday) use ($expectedChristmasDate) {
            return $holiday['date'] === $expectedChristmasDate;
        });

        $this->assertNotEmpty($christmasHolidays, "Christmas Day {$expectedChristmasDate} should be present in England and Wales holidays");

        $christmasHoliday = reset($christmasHolidays);
        $this->assertArrayHasKey('date', $christmasHoliday);
        $this->assertArrayHasKey('title', $christmasHoliday);
        $this->assertStringContainsString('Christmas', $christmasHoliday['title']);
    }
}
