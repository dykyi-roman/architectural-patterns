<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrderHistory\Dto;

final readonly class EventDto implements \JsonSerializable
{
    public function __construct(
        private string $eventId,
        private \DateTimeInterface $occurredOn,
        private string $eventName,
        private array $eventData,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'event_id' => $this->eventId,
            'occurred_at' => $this->occurredOn->format('c'),
            'event_type' => $this->eventName,
            'data' => $this->eventData,
        ];
    }
}
