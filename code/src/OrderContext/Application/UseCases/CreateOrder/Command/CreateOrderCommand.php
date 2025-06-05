<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\Entity\OrderItem;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * @see CreateOrderCommandHandler
 */
final readonly class CreateOrderCommand
{
    /**
     * @var OrderItem[]
     */
    private array $orderItems;

    public function __construct(
        public OrderId $orderId,
        public CustomerId $customerId,
        OrderItem ...$orderItems,
    ) {
        $this->orderItems = $orderItems;
    }

    public function getOrderItems(): array
    {
        return $this->orderItems;
    }
}
