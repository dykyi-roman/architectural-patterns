<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use OrderContext\DomainModel\Event\DomainEventInterface;

/**
 * Интерфейс для публикации сообщений через паттерн Outbox
 */
interface OutboxPublisherInterface
{
    /**
     * @throws \RuntimeException При ошибке публикации
     */
    public function publish(DomainEventInterface $event): void;
}
