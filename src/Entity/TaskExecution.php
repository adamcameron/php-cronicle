<?php

namespace App\Entity;

use App\Repository\TaskExecutionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: TaskExecutionRepository::class)]
class TaskExecution implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: DynamicTaskMessage::class, inversedBy: 'execution')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DynamicTaskMessage $task = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $nextScheduledAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $executedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $executionTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastResult = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): ?DynamicTaskMessage
    {
        return $this->task;
    }

    public function setTask(DynamicTaskMessage $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getNextScheduledAt(): ?DateTimeImmutable
    {
        return $this->nextScheduledAt;
    }

    public function setNextScheduledAt(?DateTimeImmutable $nextScheduledAt): static
    {
        $this->nextScheduledAt = $nextScheduledAt;

        return $this;
    }

    public function getExecutedAt(): ?DateTimeImmutable
    {
        return $this->executedAt;
    }

    public function setExecutedAt(?DateTimeImmutable $executedAt): static
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?int $executionTime): static
    {
        $this->executionTime = $executionTime;

        return $this;
    }

    public function getLastResult(): ?string
    {
        return $this->lastResult;
    }

    public function setLastResult(?string $lastResult): static
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'taskId' => $this->task?->getId(),
            'nextScheduledAt' => $this->nextScheduledAt?->format(DATE_ATOM),
            'executedAt' => $this->executedAt?->format(DATE_ATOM),
            'executionTime' => $this->executionTime,
            'lastResult' => $this->lastResult,
        ];
    }
}
