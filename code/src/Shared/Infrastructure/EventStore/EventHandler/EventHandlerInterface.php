<?php

declare(strict_types=1);

namespace Shared\Infrastructure\EventStore\EventHandler;

use Shared\DomainModel\Event\DomainEventInterface;

interface EventHandlerInterface
{
    /**
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function handle(DomainEventInterface $event): void;

    public function canHandle(DomainEventInterface $event): bool;
}
