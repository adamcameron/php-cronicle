<?php

namespace App\Service;

use App\Enum\TaskTimezone;
use DateTimeImmutable;
use DateTimeZone;

class ScheduleTimezoneConverter
{
    public function __construct(
        private readonly ScheduleFormatDetector $scheduleFormatDetector
    ) {}

    public function convertToUtc(string $schedule, TaskTimezone $timezone): string
    {
        if ($timezone === TaskTimezone::UTC) {
            return $schedule;
        }

        if ($timezone === TaskTimezone::EUROPE_LONDON && $this->isCurrentlyBST()) {
            return $this->subtractHourFromCron($schedule);
        }

        return $schedule;
    }

    private function isCurrentlyBST(): bool
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/London'));
        $utcNow = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        
        $londonOffset = $now->getOffset();
        $utcOffset = $utcNow->getOffset();
        
        return ($londonOffset - $utcOffset) === 3600;
    }

    private function subtractHourFromCron(string $cronExpression): string
    {
        if (!$this->scheduleFormatDetector->isCronExpression($cronExpression)) {
            return $cronExpression;
        }

        $parts = explode(' ', $cronExpression);
        if (count($parts) < 5) {
            return $cronExpression;
        }

        $hour = $parts[1];
        
        if (is_numeric($hour)) {
            $newHour = ((int)$hour - 1 + 24) % 24;
            $parts[1] = (string)$newHour;
        } elseif (str_contains($hour, '-')) {
            [$start, $end] = explode('-', $hour);
            if (is_numeric($start) && is_numeric($end)) {
                $newStart = ((int)$start - 1 + 24) % 24;
                $newEnd = ((int)$end - 1 + 24) % 24;
                $parts[1] = $newStart . '-' . $newEnd;
            }
        } elseif (str_contains($hour, ',')) {
            $hours = explode(',', $hour);
            $adjustedHours = [];
            foreach ($hours as $h) {
                if (is_numeric($h)) {
                    $adjustedHours[] = ((int)$h - 1 + 24) % 24;
                } else {
                    $adjustedHours[] = $h;
                }
            }
            $parts[1] = implode(',', $adjustedHours);
        }

        return implode(' ', $parts);
    }
}
