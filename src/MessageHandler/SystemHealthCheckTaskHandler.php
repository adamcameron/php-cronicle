<?php

namespace App\MessageHandler;

use App\Entity\DynamicTaskMessage;

class SystemHealthCheckTaskHandler extends AbstractTaskHandler
{
    protected function handle(DynamicTaskMessage $task): string
    {
        // Task logic here - logging is handled by parent class

        return 'success';
    }
}
