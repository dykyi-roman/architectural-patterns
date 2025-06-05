<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

use Shared\DomainModel\Event\DomainEventInterface;

abstract readonly class AbstractDomainEvent implements DomainEventInterface
{
    public function __construct(
        private string $eventId,
        private \DateTimeImmutable $occurredOn,
        private string $aggregateId,
    ) {
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function getAggregateId(): string
    {
        return $this->aggregateId;
    }

    public function getEventName(): string
    {
        return static::class;
    }

    public function jsonSerialize(): array
    {
        return [
            'event_id' => $this->eventId,
            'occurred_at' => $this->occurredOn->format('Y-m-d\TH:i:s.uP'),
            'aggregate_id' => $this->aggregateId,
            'event_name' => $this->getEventName(),
        ];
    }
}
