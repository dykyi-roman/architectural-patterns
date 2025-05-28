<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore;

use OrderContext\DomainModel\ValueObject\OrderId;
use Shared\DomainModel\Event\DomainEventInterface;

/**
 * Интерфейс хранилища событий
 */
interface EventStoreInterface
{
    /**
     * Добавляет событие в хранилище
     *
     * @throws \RuntimeException При ошибке сохранения события
     */
    public function append(DomainEventInterface $event): void;

    /**
     * Получает события для конкретного агрегата
     *
     * @param OrderId $aggregateId Идентификатор агрегата
     * @return array<DomainEventInterface> Массив событий
     * @throws \RuntimeException При ошибке чтения событий
     */
    public function getEventsForAggregate(OrderId $aggregateId): array;
    
    /**
     * Получает все события определенного типа
     *
     * @param string $eventType Тип события
     * @return array<DomainEventInterface> Массив событий
     * @throws \RuntimeException При ошибке чтения событий
     */
    public function getEventsByType(string $eventType): array;
}
