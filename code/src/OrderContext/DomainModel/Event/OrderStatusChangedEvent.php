<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

use OrderContext\DomainModel\Enum\OrderStatus;
use OrderContext\DomainModel\ValueObject\OrderId;

final readonly class OrderStatusChangedEvent extends AbstractDomainEvent
{
    public function __construct(
        string $eventId,
        \DateTimeImmutable $occurredOn,
        private OrderId $orderId,
        private OrderStatus $previousStatus,
        private OrderStatus $newStatus,
    ) {
        parent::__construct($eventId, $occurredOn, $orderId->toString());
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getPreviousStatus(): OrderStatus
    {
        return $this->previousStatus;
    }

    public function getNewStatus(): OrderStatus
    {
        return $this->newStatus;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'order_id' => $this->orderId->toString(),
            'previous_status' => $this->previousStatus->value,
            'new_status' => $this->newStatus->value,
        ]);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public static function fromArray(array $data): static
    {
        return new self(
            $data['event_id'],
            new \DateTimeImmutable($data['occurred_on']),
            OrderId::fromString($data['order_id']),
            OrderStatus::fromString($data['previous_status']),
            OrderStatus::fromString($data['new_status'])
        );
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->getOccurredOn();
    }
}
