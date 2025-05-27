<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use OrderContext\DomainModel\Event\DomainEventInterface;

interface OutboxPublisherInterface
{
    /**
     * @throws \RuntimeException
     */
    public function publish(DomainEventInterface $event): void;
}
