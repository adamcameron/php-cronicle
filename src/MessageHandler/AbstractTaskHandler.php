<?php

namespace App\MessageHandler;

use App\Entity\DynamicTaskMessage;
use App\Entity\TaskExecution;
use App\Message\ScheduleReloadMessage;
use App\Service\ScheduleFormatDetector;
use App\Service\ScheduleTimezoneConverter;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Scheduler\Trigger\PeriodicalTrigger;
use Throwable;
use DateTimeImmutable;

#[AutoconfigureTag('app.scheduled_task')]
abstract class AbstractTaskHandler
{
    private const int MAX_FAILURES = 3;

    public function __construct(
        private readonly LoggerInterface $tasksLogger,
        private readonly EntityManagerInterface $entityManager,
        private readonly ScheduleFormatDetector $scheduleFormatDetector,
        private readonly ScheduleTimezoneConverter $scheduleTimezoneConverter,
        private readonly MessageBusInterface $bus
    ) {}

    abstract protected function handle(DynamicTaskMessage $task): string;

    public function execute(DynamicTaskMessage $task): void
    {
        $startTime = new DateTimeImmutable();
        $executionStart = microtime(true);
        $this->tasksLogger->info('Task started', ['task' => $task]);

        try {
            $result = $this->handle($task);
            $executionTime = (int) round((microtime(true) - $executionStart) * 1000);
            $this->recordSuccessfulExecution($task, $startTime, $executionTime, $result);
            return;
        } catch (Throwable $e) {
            $executionTime = (int) round((microtime(true) - $executionStart) * 1000);
            $this->recordFailedExecution($task, $startTime, $executionTime, $e->getMessage());
            throw $e;
        }
    }

    private function recordSuccessfulExecution(
        DynamicTaskMessage $task,
        DateTimeImmutable $executedAt,
        int $executionTime,
        string $result
    ): void {
        $this->tasksLogger->info('Task completed successfully', [
            'task' => $task,
            'execution_time_ms' => $executionTime,
            'result' => $result
        ]);

        $this->updateExecutionBase($task, $executedAt, $executionTime, $result, 0);
        $this->entityManager->flush();
    }

    private function recordFailedExecution(
        DynamicTaskMessage $task,
        DateTimeImmutable $executedAt,
        int $executionTime,
        string $errorMessage
    ): void {
        $this->tasksLogger->error('Task failed', [
            'task' => $task,
            'execution_time_ms' => $executionTime,
            'error' => $errorMessage
        ]);

        $execution = $this->updateExecutionBase($task, $executedAt, $executionTime, $errorMessage);

        if ($execution->getFailureCount() < self::MAX_FAILURES) {
            $this->entityManager->flush();
            return;
        }

        // Get the re-fetched task from the execution relationship
        $managedTask = $execution->getTask();
        $managedTask->setActive(false);

        $this->entityManager->persist($managedTask);
        $this->entityManager->flush();
        $this->tasksLogger->critical('Task deactivated after ' . self::MAX_FAILURES . ' failures', [
            'task' => $managedTask,
            'execution' => $execution
        ]);
        $this->bus->dispatch(new ScheduleReloadMessage('Task deactivated after ' . self::MAX_FAILURES . ' failures'));
    }

    private function updateExecutionBase(
        DynamicTaskMessage $task,
        DateTimeImmutable $executedAt,
        int $executionTime,
        string $result,
        ?int $newFailureCount = null
    ): TaskExecution {
        // Re-fetch to get managed entities after serialization/deserialization
        $task = $this->entityManager->find(DynamicTaskMessage::class, $task->getId());

        $execution = $task->getExecution();
        if (!$execution) {
            $execution = new TaskExecution();
            $execution->setTask($task);
        }

        $execution->setExecutedAt($executedAt);
        $execution->setExecutionTime($executionTime);
        $execution->setLastResult($result);

        $failureCount = $newFailureCount ?? ($execution->getFailureCount() + 1);
        $execution->setFailureCount($failureCount);
        $nextScheduledAt = $this->calculateNextScheduledAt($task, $executedAt);
        $execution->setNextScheduledAt($nextScheduledAt);
        $this->entityManager->persist($execution);

        return $execution;
    }

    private function calculateNextScheduledAt(
        DynamicTaskMessage $task,
        DateTimeImmutable $fromTime
    ): DateTimeImmutable {
        // Convert schedule to UTC just like DynamicScheduleProvider does
        $schedule = $this->scheduleTimezoneConverter->convertToUtc(
            $task->getSchedule(),
            $task->getTimezone()
        );
        if ($this->scheduleFormatDetector->isCronExpression($schedule)) {
            $cron = new CronExpression($schedule);
            return DateTimeImmutable::createFromMutable($cron->getNextRunDate($fromTime));
        }

        $trigger = new PeriodicalTrigger($schedule);
        $nextRun = $trigger->getNextRunDate($fromTime);
        if ($nextRun === null) {
            throw new RuntimeException("PeriodicalTrigger returned null for schedule: $schedule");
        }
        return $nextRun;
    }

    public static function getTaskTypeFromClassName(): string
    {
        $classNameOnly = substr(static::class, strrpos(static::class, '\\') + 1);
        $taskNamePart = str_replace('TaskHandler', '', $classNameOnly);

        $snakeCase = strtolower(preg_replace('/([A-Z])/', '_$1', $taskNamePart));

        return ltrim($snakeCase, '_');
    }
}
