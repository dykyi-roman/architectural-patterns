<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Exception;

use OrderContext\DomainModel\ValueObject\OrderId;

final class OrderNotFoundException extends \DomainException
{
    public function __construct(OrderId $orderId)
    {
        parent::__construct(sprintf('Order not found, id: %s', $orderId->toString()));
    }
}