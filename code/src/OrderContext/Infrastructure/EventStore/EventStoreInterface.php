<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore;

use OrderContext\DomainModel\Model\Event\DomainEvent;
use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * Интерфейс хранилища событий
 */
interface EventStoreInterface
{
    /**
     * Добавляет событие в хранилище
     *
     * @param DomainEvent $event Доменное событие
     * @return void
     * @throws \RuntimeException При ошибке сохранения события
     */
    public function append(DomainEvent $event): void;

    /**
     * Получает события для конкретного агрегата
     *
     * @param OrderId $aggregateId Идентификатор агрегата
     * @return array<DomainEvent> Массив событий
     * @throws \RuntimeException При ошибке чтения событий
     */
    public function getEventsForAggregate(OrderId $aggregateId): array;
    
    /**
     * Получает все события определенного типа
     *
     * @param string $eventType Тип события
     * @return array<DomainEvent> Массив событий
     * @throws \RuntimeException При ошибке чтения событий
     */
    public function getEventsByType(string $eventType): array;
}
