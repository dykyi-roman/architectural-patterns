<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrderHistory\Exception;

use OrderContext\DomainModel\Enum\OrderErrorCodes;
use OrderContext\DomainModel\ValueObject\OrderId;
use Shared\Application\Exception\ApplicationException;

final class HistoryNotFoundException extends ApplicationException
{
    public function __construct(OrderId $orderId)
    {
        parent::__construct(
            get_class($this),
            OrderErrorCodes::HISTORY_NOT_FOUND,
            sprintf('Order history with ID %s not found', $orderId->toString()),
        );
    }
}
