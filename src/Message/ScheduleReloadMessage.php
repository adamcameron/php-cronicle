<?php

namespace App\Message;

class ScheduleReloadMessage
{
    /** @codeCoverageIgnore */
    public function __construct(
        private readonly string $reason = 'Schedule change detected'
    ) {}

    /** @codeCoverageIgnore */
    public function getReason(): string
    {
        return $this->reason;
    }
}
