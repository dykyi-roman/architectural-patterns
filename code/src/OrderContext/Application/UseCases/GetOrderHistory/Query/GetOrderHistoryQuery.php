<?php

namespace OrderContext\Application\UseCases\GetOrderHistory\Query;

use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * @see GetOrderHistoryQueryHandler
 */
readonly class GetOrderHistoryQuery
{
    public function __construct(
        public OrderId $orderId,
    ) {
    }
}