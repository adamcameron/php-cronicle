<?php

namespace App\MessageHandler;

use App\Message\TaskMessage;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TaskMessageHandler
{
    private array $handlerMap = [];

    public function __construct(
        #[AutowireIterator(
            tag: 'app.scheduled_task',
            defaultIndexMethod: 'getTaskTypeFromClassName'
        )] iterable $taskHandlers
    ) {
        $this->handlerMap = iterator_to_array($taskHandlers);
    }

    public function __invoke(TaskMessage $message): void
    {
        $taskType = $message->getTaskType();
        
        if (!isset($this->handlerMap[$taskType])) {
            throw new InvalidArgumentException(
                sprintf('No handler found for task type "%s"', $taskType)
            );
        }

        /** @var AbstractTaskHandler $handler */
        $handler = $this->handlerMap[$taskType];
        $handler->execute($message->dynamicTask);
    }
}
