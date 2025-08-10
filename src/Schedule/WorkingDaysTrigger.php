<?php

namespace App\Schedule;

use App\Repository\BankHolidayRepository;
use DateTimeImmutable;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

class WorkingDaysTrigger implements TriggerInterface
{
    public function __construct(
        private readonly TriggerInterface $innerTrigger,
        private readonly BankHolidayRepository $bankHolidayRepository
    ) {}

    public function getNextRunDate(DateTimeImmutable $run): ?DateTimeImmutable
    {
        $nextRun = $this->innerTrigger->getNextRunDate($run);
        
        if ($nextRun === null) {
            return null;
        }
        
        while ($this->isNonWorkingDay($nextRun)) {
            $nextDay = $nextRun->modify('+1 day');
            $nextRun = $this->innerTrigger->getNextRunDate($nextDay);
            
            if ($nextRun === null) {
                return null;
            }
        }
        
        return $nextRun;
    }
    
    public function __toString(): string
    {
        return sprintf('WorkingDays(%s)', $this->innerTrigger);
    }
    
    private function isNonWorkingDay(DateTimeImmutable $date): bool
    {
        if (in_array($date->format('N'), [6, 7])) {
            return true;
        }
        
        return $this->bankHolidayRepository->isHoliday($date);
    }
}
