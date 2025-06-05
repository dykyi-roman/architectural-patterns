<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Event;

use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\Money;
use OrderContext\DomainModel\ValueObject\OrderId;
use Shared\DomainModel\Event\AbstractDomainEvent;

final readonly class OrderCreatedEvent extends AbstractDomainEvent
{
    /**
     * @param array<array{product_id: string, quantity: int, price: array{amount: int, currency: string}}> $items
     */
    public function __construct(
        string $eventId,
        \DateTimeImmutable $occurredAt,
        private OrderId $orderId,
        private CustomerId $customerId,
        private Money $totalAmount,
        private array $items,
    ) {
        parent::__construct($eventId, $orderId->toString(), $occurredAt);
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
            'total_amount' => $this->totalAmount->jsonSerialize(),
            'items' => array_map(static function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => [
                        'amount' => $item['price']['amount'],
                        'currency' => $item['price']['currency'],
                    ],
                ];
            }, $this->items),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \DateMalformedStringException
     */
    public static function fromArray(array $data): static
    {
        $totalAmount = null;
        if (isset($data['total_amount'])) {
            if (is_array($data['total_amount'])) {
                $totalAmount = Money::fromAmount(
                    $data['total_amount']['amount'],
                    $data['total_amount']['currency']
                );
            } elseif (is_numeric($data['total_amount'])) {
                $totalAmount = Money::fromAmount((int) $data['total_amount'], 'USD');
            } else {
                throw new \InvalidArgumentException('Invalid total_amount format');
            }
        } else {
            $totalAmount = Money::fromAmount(0, 'USD');
        }

        return new self(
            $data['event_id'],
            new \DateTimeImmutable($data['occurred_at']),
            OrderId::fromString($data['order_id']),
            CustomerId::fromString($data['customer_id']),
            $totalAmount,
            $data['items'] ?? []
        );
    }
}
