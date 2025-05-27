<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

abstract readonly class AbstractDomainEvent implements DomainEventInterface
{
    /**
     * @param string $eventId Уникальный идентификатор события
     * @param \DateTimeImmutable $occurredOn Дата и время возникновения события
     * @param string $aggregateId Идентификатор агрегата, который вызвал событие
     */
    public function __construct(
        private string $eventId,
        private \DateTimeImmutable $occurredOn,
        private string $aggregateId
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
            'occurred_on' => $this->occurredOn->format('Y-m-d\TH:i:s.uP'),
            'aggregate_id' => $this->aggregateId,
            'event_name' => $this->getEventName(),
        ];
    }
}
