<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore\EventHandler;

use OrderContext\DomainModel\Model\Event\DomainEvent;

/**
 * Интерфейс для обработчиков доменных событий
 */
interface EventHandlerInterface
{
    /**
     * Обрабатывает доменное событие
     *
     * @param DomainEvent $event Доменное событие для обработки
     * @return void
     * @throws \InvalidArgumentException Если событие не может быть обработано этим обработчиком
     * @throws \RuntimeException При ошибке обработки события
     */
    public function handle(DomainEvent $event): void;

    /**
     * Проверяет, может ли данный обработчик обработать событие
     *
     * @param DomainEvent $event Доменное событие для проверки
     * @return bool Может ли обработчик обработать событие
     */
    public function canHandle(DomainEvent $event): bool;
}
