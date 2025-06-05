<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\OrderId;

final readonly class OrderCreatedEvent extends AbstractDomainEvent
{
    /**
     * @param array<array{product_id: string, quantity: int, price: array{amount: int, currency: string}}> $items
     */
    public function __construct(
        string $eventId,
        \DateTimeImmutable $occurredOn,
        private OrderId $orderId,
        private CustomerId $customerId,
        private Money $totalAmount,
        private array $items,
    ) {
        parent::__construct($eventId, $occurredOn, $orderId->toString());
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    public function getTotalAmount(): Money
    {
        return $this->totalAmount;
    }

    /**
     * @return array<array{product_id: string, quantity: int, price: array{amount: int, currency: string}}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'order_id' => $this->orderId->toString(),
            'customer_id' => $this->customerId->toString(),
            'total_amount' => [
                'amount' => $this->totalAmount->getAmount(),
                'currency' => $this->totalAmount->getCurrency(),
            ],
            'items' => $this->items,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \DateMalformedStringException
     */
    public static function fromArray(array $data): static
    {
        return new self(
            $data['event_id'],
            new \DateTimeImmutable($data['occurred_on']),
            OrderId::fromString($data['order_id']),
            CustomerId::fromString($data['customer_id']),
            Money::fromAmount($data['total_amount']['amount'], $data['total_amount']['currency']),
            $data['items']
        );
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->getOccurredOn();
    }
}
