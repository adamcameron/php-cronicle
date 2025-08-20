<?php

namespace App\MessageHandler;

use App\Entity\DynamicTaskMessage;

class NullTaskHandler extends AbstractTaskHandler
{

    /** @codeCoverageIgnore */
    public function handle(DynamicTaskMessage $task): string
    {
        return 'success';
    }
}
