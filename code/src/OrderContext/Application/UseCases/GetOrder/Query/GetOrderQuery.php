<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrder\Query;

use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * @see GetOrderQueryHandler
 */
final readonly class GetOrderQuery
{
    public function __construct(
        private OrderId $orderId,
    ) {
    }

    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }
}
