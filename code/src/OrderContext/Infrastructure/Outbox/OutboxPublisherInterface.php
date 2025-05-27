<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use OrderContext\DomainModel\Model\Event\DomainEvent;

/**
 * Интерфейс для публикации сообщений через паттерн Outbox
 */
interface OutboxPublisherInterface
{
    /**
     * Публикует событие в outbox для последующей отправки
     *
     * @param DomainEvent $event Событие для публикации
     * @return void
     * @throws \RuntimeException При ошибке публикации
     */
    public function publish(DomainEvent $event): void;
}
