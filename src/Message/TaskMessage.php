<?php

namespace App\Message;

use App\Entity\DynamicTaskMessage;

class TaskMessage
{
    public function __construct(
        public readonly DynamicTaskMessage $dynamicTask
    ) {}

    // Convenience methods for backward compatibility and easy access
    public function getTaskType(): string
    {
        return $this->dynamicTask->getType();
    }

    public function getTaskId(): int
    {
        return $this->dynamicTask->getId();
    }

    public function getMetadata(): ?array
    {
        return $this->dynamicTask->getMetadata();
    }
}
