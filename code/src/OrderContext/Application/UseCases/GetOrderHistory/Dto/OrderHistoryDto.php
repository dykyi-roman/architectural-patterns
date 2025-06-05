<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrderHistory\Dto;

use OrderContext\DomainModel\ValueObject\OrderId;

final readonly class OrderHistoryDto implements \JsonSerializable
{
    /**
     * @param array<EventDto> $events
     */
    public function __construct(
        private OrderId $orderId,
        private array $events,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'order_id' => $this->orderId->toString(),
            'events' => array_map(fn (EventDto $event) => $event->jsonSerialize(), $this->events),
        ];
    }
}
