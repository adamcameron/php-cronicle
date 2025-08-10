<?php

namespace App\MessageHandler;

class DataBackupTaskHandler extends AbstractTaskHandler
{
    protected function handle(int $taskId, array $metadata): void
    {
        // Task logic here - logging is handled by parent class
    }
}
