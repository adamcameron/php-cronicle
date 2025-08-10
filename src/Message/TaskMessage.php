<?php

namespace App\Message;

class TaskMessage
{
    public function __construct(
        public readonly string $taskType,
        public readonly int $taskId,
        public readonly array $metadata
    ) {}
}
