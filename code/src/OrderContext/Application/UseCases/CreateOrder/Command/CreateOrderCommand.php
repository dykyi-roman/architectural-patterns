<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Entity\OrderItem;
use OrderContext\DomainModel\ValueObject\CustomerId;
use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * @see \OrderContext\Application\UseCases\CreateOrder\Command\CreateOrderCommandHandler
 */
final readonly class CreateOrderCommand
{
    /**
     * @var \OrderContext\DomainModel\Entity\OrderItem[]
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
