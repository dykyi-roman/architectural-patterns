<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrder\Query;

use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * @see \OrderContext\Application\UseCases\GetOrder\Query\GetOrderQueryHandler
 */
final readonly class GetOrderQuery
{
    public function __construct(
        private OrderId $orderId
    ) {
    }

    /**
     * Returns order identifier
     */
    public function getOrderId(): OrderId
    {
        return $this->orderId;
    }
}
