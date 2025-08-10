<?php

namespace App\Entity;

use App\Enum\TaskTimezone;
use App\Repository\DynamicTaskMessageRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity(repositoryClass: DynamicTaskMessageRepository::class)]
class DynamicTaskMessage implements JsonSerializable
{
    public const int DEFAULT_PRIORITY = 50;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(length: 500, nullable: false)]
    private ?string $schedule = null;

    #[ORM\Column(nullable: false, enumType: TaskTimezone::class)]
    private ?TaskTimezone $timezone = null;

    #[ORM\Column(nullable: false, options: ['default' => self::DEFAULT_PRIORITY])]
    private ?int $priority = self::DEFAULT_PRIORITY;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    private ?bool $active = true;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private ?bool $workingDaysOnly = false;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $executedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $executionTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $lastResult = null;

    #[ORM\Column(nullable: true)]
    private ?array $metadata = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(string $schedule): static
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getTimezone(): ?TaskTimezone
    {
        return $this->timezone;
    }

    public function setTimezone(TaskTimezone $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getScheduledAt(): ?DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

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

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function isWorkingDaysOnly(): ?bool
    {
        return $this->workingDaysOnly;
    }

    public function setWorkingDaysOnly(bool $workingDaysOnly): static
    {
        $this->workingDaysOnly = $workingDaysOnly;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
       return [
        'id' => $this->id,
        'type' => $this->type,
        'name' => $this->name,
        'schedule' => $this->schedule,
        'timezone' => $this->timezone?->value,
        'priority' => $this->priority,
        'active' => $this->active,
        'workingDaysOnly' => $this->workingDaysOnly,
        'scheduledAt' => $this->scheduledAt?->format(DateTimeImmutable::ATOM),
        'executedAt' => $this->executedAt?->format(DateTimeImmutable::ATOM),
        'executionTime' => $this->executionTime,
        'lastResult' => $this->lastResult,
        'metadata' => $this->metadata
       ];
    }
}
