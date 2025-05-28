<?php

declare(strict_types=1);

namespace Shared\DomainModel\Service;

use Shared\DomainModel\Event\DomainEventInterface;

interface OutboxPublisherInterface
{
    /**
     * @throws \RuntimeException
     */
    public function publish(DomainEventInterface $event): void;
}
