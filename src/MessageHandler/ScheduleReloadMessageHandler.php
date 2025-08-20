<?php

namespace App\MessageHandler;

use App\Message\ScheduleReloadMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ScheduleReloadMessageHandler
{
    /** @codeCoverageIgnore */
    public function __construct(
        private readonly LoggerInterface $tasksLogger,
        private readonly string $restartFilePath
    ) {}

    /** @codeCoverageIgnore */
    public function __invoke(ScheduleReloadMessage $message): void
    {
        $this->tasksLogger->info('Schedule reload requested, triggering worker restart', [
            'reason' => $message->getReason()
        ]);
        file_put_contents($this->restartFilePath, time());
    }
}
