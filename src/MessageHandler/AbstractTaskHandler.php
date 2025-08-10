<?php

namespace App\MessageHandler;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;

#[AutoconfigureTag('app.scheduled_task')]
abstract class AbstractTaskHandler
{
    public function __construct(
        private readonly LoggerInterface $tasksLogger
    ) {}

    public function execute(int $taskId, array $metadata): void
    {
        $this->tasksLogger->info('Task started', [
            'task_id' => $taskId,
            'task_type' => $this->getTaskTypeFromClassName(),
            'metadata' => $metadata
        ]);

        try {
            $this->handle($taskId, $metadata);
            
            $this->tasksLogger->info('Task completed successfully', [
                'task_id' => $taskId,
                'task_type' => $this->getTaskTypeFromClassName()
            ]);
        } catch (Throwable $e) {
            $this->tasksLogger->error('Task failed', [
                'task_id' => $taskId,
                'task_type' => $this->getTaskTypeFromClassName(),
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
            throw $e;
        }
    }

    abstract protected function handle(int $taskId, array $metadata): void;

    public static function getTaskTypeFromClassName(): string
    {
        $classNameOnly = substr(static::class, strrpos(static::class, '\\') + 1);
        $taskNamePart = str_replace('TaskHandler', '', $classNameOnly);
        
        $snakeCase = strtolower(preg_replace('/([A-Z])/', '_$1', $taskNamePart));
        return ltrim($snakeCase, '_');
    }
}
