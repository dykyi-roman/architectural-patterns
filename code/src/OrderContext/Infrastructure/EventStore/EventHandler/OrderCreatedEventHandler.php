<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore\EventHandler;

use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\Infrastructure\Persistence\Doctrine\Repository\ElasticsearchOrderReadModelRepository;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Event\DomainEventInterface;
use Shared\Infrastructure\EventStore\EventHandler\EventHandlerInterface;

final readonly class OrderCreatedEventHandler implements EventHandlerInterface
{
    /**
     * @param ElasticsearchOrderReadModelRepository $readModelRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private ElasticsearchOrderReadModelRepository $readModelRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(DomainEventInterface $event): void
    {
        if (!$event instanceof OrderCreatedEvent) {
            throw new \InvalidArgumentException(
                sprintf('Ожидается событие типа %s, получено %s', OrderCreatedEvent::class, get_class($event))
            );
        }

        $this->logger->info(
            'Обработка события создания заказа для обновления read-модели',
            ['order_id' => $event->getOrderId()->toString()]
        );

        // Формируем данные для индексации в Elasticsearch
        $orderData = [
            'id' => $event->getOrderId()->toString(),
            'customer_id' => $event->getCustomerId()->toString(),
            'status' => 'created', // Начальный статус для нового заказа
            'total_amount' => [
                'amount' => $event->getTotalAmount()->getAmount(),
                'currency' => $event->getTotalAmount()->getCurrency(),
            ],
            'items' => $event->getItems(),
            'created_at' => $event->getOccurredOn()->format('c'),
            'updated_at' => null,
        ];

        // Индексируем данные в Elasticsearch
        $this->readModelRepository->index($orderData);

        $this->logger->info(
            'Заказ успешно проиндексирован в read-модели',
            ['order_id' => $event->getOrderId()->toString()]
        );
    }

    public function canHandle(DomainEventInterface $event): bool
    {
        return $event instanceof OrderCreatedEvent;
    }
}
