<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\ValueObject\CustomerId;

/**
 * @see \OrderContext\Application\UseCases\CreateOrder\Command\CreateOrderCommandHandler
 */
final readonly class CreateOrderCommand
{
    /**
     * @param array<array{product_id: mixed, quantity: int, price: mixed}> $items Order items
     */
    public function __construct(
        private CustomerId $customerId,
        private array $items
    ) {
    }

    /**
     * Returns customer identifier
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * Returns order items
     *
     * @return array<array{product_id: mixed, quantity: int, price: mixed}>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
