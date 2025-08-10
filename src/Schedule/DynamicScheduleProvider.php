<?php

namespace App\Schedule;

use App\Entity\DynamicTaskMessage;
use App\Message\TaskMessage;
use App\Repository\BankHolidayRepository;
use App\Repository\DynamicTaskMessageRepository;
use App\Service\ScheduleFormatDetector;
use App\Service\ScheduleTimezoneConverter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('dynamic')]
class DynamicScheduleProvider implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function __construct(
        private readonly DynamicTaskMessageRepository $taskRepository,
        private readonly ScheduleFormatDetector $scheduleFormatDetector,
        private readonly ScheduleTimezoneConverter $scheduleTimezoneConverter,
        private readonly BankHolidayRepository $bankHolidayRepository,
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $tasksLogger
    ) {}

    public function getSchedule(): Schedule
    {
        if ($this->schedule !== null) {
            return $this->schedule;
        }

        $this->tasksLogger->info('Rebuilding schedule from database');

        $this->schedule = new Schedule();
        $this->schedule->stateful($this->cache);
        $this->schedule->processOnlyLastMissedRun(true);
        
        $this->addTasksToSchedule();

        return $this->schedule;
    }

    private function addTasksToSchedule(): void
    {
        $tasks = $this->taskRepository->findActiveTasksForScheduling();

        foreach ($tasks as $task) {
            $message = $this->createRecurringMessage($task);
            $this->schedule->add($message);
        }

        $this->tasksLogger->info('Schedule rebuilt with active tasks', [
            'task_count' => count($tasks)
        ]);
    }

    private function createRecurringMessage(DynamicTaskMessage $task): RecurringMessage
    {
        $taskMessage = new TaskMessage(
            $task->getType(),
            $task->getId(),
            $task->getMetadata() ?? []
        );

        $schedule = $this->scheduleTimezoneConverter->convertToUtc(
            $task->getSchedule(),
            $task->getTimezone()
        );

        $scheduleHandler = $this->scheduleFormatDetector->isCronExpression($schedule)
            ? 'cron'
            : 'every';

        $recurringMessage = RecurringMessage::$scheduleHandler($schedule, $taskMessage);

        if ($task->isWorkingDaysOnly()) {
            $workingDaysTrigger = new WorkingDaysTrigger(
                $recurringMessage->getTrigger(),
                $this->bankHolidayRepository
            );
            return RecurringMessage::trigger($workingDaysTrigger, $taskMessage);
        }

        return $recurringMessage;
    }
}
