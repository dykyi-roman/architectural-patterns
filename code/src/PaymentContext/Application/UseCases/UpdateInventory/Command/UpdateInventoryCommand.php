<?php

declare(strict_types=1);

namespace PaymentContext\Application\UseCases\UpdateInventory\Command;

/**
 * @see UpdateInventoryCommandHandler
 */
final readonly class UpdateInventoryCommand
{
    public function __construct(
        public string $orderStatus,
    ) {
    }
}