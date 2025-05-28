<?php

namespace OrderContext\Application\UseCases\GetOrderHistory\Query;

use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * @throw GetOrderHistoryQueryHandler
 */
readonly class GetOrderHistoryQuery
{
    public function __construct(
        public OrderId $orderId,
    ) {
    }
}