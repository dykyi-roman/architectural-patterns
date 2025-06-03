<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore\EventHandler;

use OrderContext\DomainModel\Event\OrderStatusChangedEvent;
use OrderContext\Infrastructure\Persistence\Doctrine\Repository\ElasticsearchOrderReadModelRepository;
use Psr\Log\LoggerInterface;
use Shared\DomainModel\Event\DomainEventInterface;
use Shared\Infrastructure\EventStore\EventHandler\EventHandlerInterface;

final readonly class OrderStatusChangedEventHandler implements EventHandlerInterface
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

    public function handle(DomainEventInterface $event): void
    {
        if (!$event instanceof OrderStatusChangedEvent) {
            throw new \InvalidArgumentException(
                sprintf('Ожидается событие типа %s, получено %s', OrderStatusChangedEvent::class, get_class($event))
            );
        }

        $orderId = $event->getOrderId()->toString();
        $this->logger->info(
            'Обработка события изменения статуса заказа для обновления read-модели',
            [
                'order_id' => $orderId,
                'previous_status' => $event->getPreviousStatus()->value,
                'new_status' => $event->getNewStatus()->value
            ]
        );

        // Получаем текущие данные заказа из Elasticsearch
        $orderData = $this->readModelRepository->findById($event->getOrderId());
        
        if ($orderData === null) {
            $this->logger->warning(
                'Заказ не найден в read-модели при обновлении статуса',
                ['order_id' => $orderId]
            );
            return;
        }

        // Обновляем статус и дату обновления
        $orderData['status'] = $event->getNewStatus()->value;
        $orderData['updated_at'] = $event->getOccurredOn()->format('c');

        // Сохраняем обновленные данные в Elasticsearch
        $this->readModelRepository->index($orderData);

        $this->logger->info(
            'Статус заказа успешно обновлен в read-модели',
            [
                'order_id' => $orderId,
                'new_status' => $event->getNewStatus()->value
            ]
        );
    }

    public function canHandle(DomainEventInterface $event): bool
    {
        return $event instanceof OrderStatusChangedEvent;
    }
}
