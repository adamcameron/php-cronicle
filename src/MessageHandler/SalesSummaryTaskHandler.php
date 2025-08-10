<?php

namespace App\MessageHandler;

class SalesSummaryTaskHandler extends AbstractTaskHandler
{
    protected function handle(int $taskId, array $metadata): void
    {
        // Task logic here - logging is handled by parent class
    }
}
