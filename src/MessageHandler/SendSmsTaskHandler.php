<?php

namespace App\MessageHandler;

class SendSmsTaskHandler extends AbstractTaskHandler
{
    protected function handle(int $taskId, array $metadata): void
    {
        // Task logic here - logging is handled by parent class
    }
}
