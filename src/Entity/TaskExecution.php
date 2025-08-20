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

    #[ORM\Column(options: ['default' => 0])]
    private int $failureCount = 0;

    /** @codeCoverageIgnore */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @codeCoverageIgnore */
    public function getTask(): ?DynamicTaskMessage
    {
        return $this->task;
    }

    /** @codeCoverageIgnore */
    public function setTask(DynamicTaskMessage $task): static
    {
        $this->task = $task;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getNextScheduledAt(): ?DateTimeImmutable
    {
        return $this->nextScheduledAt;
    }

    /** @codeCoverageIgnore */
    public function setNextScheduledAt(?DateTimeImmutable $nextScheduledAt): static
    {
        $this->nextScheduledAt = $nextScheduledAt;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getExecutedAt(): ?DateTimeImmutable
    {
        return $this->executedAt;
    }

    /** @codeCoverageIgnore */
    public function setExecutedAt(?DateTimeImmutable $executedAt): static
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getExecutionTime(): ?int
    {
        return $this->executionTime;
    }

    /** @codeCoverageIgnore */
    public function setExecutionTime(?int $executionTime): static
    {
        $this->executionTime = $executionTime;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getLastResult(): ?string
    {
        return $this->lastResult;
    }

    /** @codeCoverageIgnore */
    public function setLastResult(?string $lastResult): static
    {
        $this->lastResult = $lastResult;

        return $this;
    }

    /** @codeCoverageIgnore */
    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    /** @codeCoverageIgnore */
    public function setFailureCount(int $failureCount): static
    {
        $this->failureCount = $failureCount;

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
            'failureCount' => $this->failureCount,
        ];
    }
}
