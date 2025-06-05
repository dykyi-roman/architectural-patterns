<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Repository;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\OrderNotFoundException;
use OrderContext\DomainModel\Exception\SaveOrderException;
use OrderContext\DomainModel\ValueObject\OrderId;
use Shared\DomainModel\Entity\AggregateRootInterface;

interface OrderWriteModelRepositoryInterface
{
    /**
     * @throws SaveOrderException
     */
    public function save(
        AggregateRootInterface $entity,
        bool $outbox = false,
        bool $events = false,
        bool $flush = true,
    ): void;

    /**
     * @throws \RuntimeException
     */
    public function exists(OrderId $orderId): bool;

    /**
     * @throw OrderNotFoundException
     */
    public function findById(OrderId $orderId): Order;
}
