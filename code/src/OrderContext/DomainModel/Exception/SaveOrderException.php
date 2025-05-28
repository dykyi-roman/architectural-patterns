<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Exception;

use OrderContext\DomainModel\Enum\OrderErrorCodes;
use Shared\DomainModel\Exception\DomainException;

final class SaveOrderException extends DomainException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct(
            OrderErrorCodes::SAVE_ORDER_ERROR,
            sprintf('Failed to save order: %s', $message),
            [],
            $previous,
        );
    }
}