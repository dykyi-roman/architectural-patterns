<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;

final readonly class OrderStatusChangedEvent extends AbstractDomainEvent
{
    /**
     * @param string $eventId Уникальный идентификатор события
     * @param \DateTimeImmutable $occurredOn Дата и время возникновения события
     * @param OrderId $orderId Идентификатор заказа
     * @param OrderStatus $previousStatus Предыдущий статус заказа
     * @param OrderStatus $newStatus Новый статус заказа
     */
    public function __construct(
        string $eventId,
        \DateTimeImmutable $occurredOn,
        private OrderId $orderId,
        private OrderStatus $previousStatus,
        private OrderStatus $newStatus
    ) {
        parent::__construct($eventId, $occurredOn, $orderId->toString());
    }

    /**
     * Возвращает идентификатор заказа
     *
     * @return OrderId
     */
    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    /**
     * Возвращает предыдущий статус заказа
     *
     * @return OrderStatus
     */
    public function getPreviousStatus(): OrderStatus
    {
        return $this->previousStatus;
    }

    /**
     * Возвращает новый статус заказа
     *
     * @return OrderStatus
     */
    public function getNewStatus(): OrderStatus
    {
        return $this->newStatus;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'order_id' => $this->orderId->toString(),
            'previous_status' => $this->previousStatus->value,
            'new_status' => $this->newStatus->value,
        ]);
    }

    /**
     * @param array<string, mixed> $data Данные события
     * @return self
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
}
