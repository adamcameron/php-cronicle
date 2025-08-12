<?php

namespace App\EventListener;

use App\Entity\DynamicTaskMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postRemove)]
class TaskChangeListener
{
    public function __construct(
        private readonly LoggerInterface $tasksLogger,
        private readonly string $restartFilePath
    ) {
        $this->ensureRestartFileExists();
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->handleTaskChange($args->getObject());
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->handleTaskChange($args->getObject());
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->handleTaskChange($args->getObject());
    }

    private function handleTaskChange($entity): void
    {
        if (!$entity instanceof DynamicTaskMessage) {
            return;
        }

        $this->tasksLogger->info('Task change detected, triggering worker restart', [
            'task_id' => $entity->getId(),
            'task_name' => $entity->getName(),
            'task_type' => $entity->getType()
        ]);

        $this->triggerWorkerRestart();
    }

    private function ensureRestartFileExists(): void
    {
        if (file_exists($this->restartFilePath)) {
            return;
        }

        $dir = dirname($this->restartFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->restartFilePath, time());
    }

    private function triggerWorkerRestart(): void
    {
        file_put_contents($this->restartFilePath, time());
        $this->tasksLogger->info('Worker restart triggered', ['timestamp' => time()]);
    }
}
