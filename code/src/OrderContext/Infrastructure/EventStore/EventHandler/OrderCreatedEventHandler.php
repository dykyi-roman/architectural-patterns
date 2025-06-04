<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore\EventHandler;

use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\Infrastructure\Persistence\Doctrine\Repository\ElasticsearchOrderReadModelRepository;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Event\DomainEventInterface;

/**
 * Обработчик события создания заказа
 */
final readonly class OrderCreatedEventHandler
{
    /**
     * @param ElasticsearchOrderReadModelRepository $readModelRepository Репозиторий для чтения модели заказа
     * @param LoggerInterface $logger Логгер
     */
    public function __construct(
        private ElasticsearchOrderReadModelRepository $readModelRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Обрабатывает событие создания заказа
     *
     * @param OrderCreatedEvent $event Событие создания заказа
     * @return void
     */
    public function __invoke(OrderCreatedEvent $event): void
    {
        $this->logger->info(
            'Обработка события создания заказа для обновления read-модели',
            ['order_id' => $event->getOrderId()->toString()]
        );

        // Создание read-модели заказа в Elasticsearch
        $this->readModelRepository->index($event->jsonSerialize());

        $this->logger->info(
            'Заказ успешно проиндексирован в read-модели',
            ['order_id' => $event->getOrderId()->toString()]
        );
    }
}
