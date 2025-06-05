<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\ChangeOrderStatus\Command;

use OrderContext\DomainModel\ValueObject\OrderId;
use OrderContext\DomainModel\ValueObject\OrderStatus;

/**
 * @see ChangeOrderStatusCommandHandler
 */
final readonly class ChangeOrderStatusCommand
{
    public function __construct(
        private OrderId $orderId,
        private OrderStatus $newStatus,
    ) {
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }

    public function getNewStatus(): OrderStatus
    {
        return $this->newStatus;
    }
}
