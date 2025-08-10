<?php

namespace App\Service;

class ScheduleFormatDetector
{
    // Cron expression regex from https://aterfax.github.io/
    private const CRON_REGEX = '/^((((\d+,)+\d+|(\d+(\/|-|#)\d+)|\d+L?|\*(\/\d+)?|L(-\d+)?|\?|[A-Z]{3}(-[A-Z]{3})?) ?){5,7})|(@(annually|yearly|monthly|weekly|daily|hourly|reboot))$/';

    public function isCronExpression(string $schedule): bool
    {
        return (bool) preg_match(self::CRON_REGEX, $schedule);
    }
}
