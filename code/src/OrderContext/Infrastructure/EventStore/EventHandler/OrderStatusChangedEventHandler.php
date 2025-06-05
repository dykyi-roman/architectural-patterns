<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore\EventHandler;

use OrderContext\DomainModel\Event\OrderStatusChangedEvent;
use OrderContext\Infrastructure\Persistence\Doctrine\Repository\ElasticsearchOrderReadModelRepository;
use Psr\Log\LoggerInterface;

final readonly class OrderStatusChangedEventHandler
{
    public function __construct(
        private ElasticsearchOrderReadModelRepository $readModelRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(OrderStatusChangedEvent $event): void
    {
        $orderId = $event->getOrderId()->toString();
        $this->logger->info(
            'Processing the order status change event to update the read model',
            [
                'order_id' => $orderId,
                'previous_status' => $event->getPreviousStatus()->value,
                'new_status' => $event->getNewStatus()->value,
            ]
        );

        $orderData = $this->readModelRepository->findById($event->getOrderId());
        if (null === $orderData) {
            $this->logger->warning(
                'Order not found in read model when updating status',
                ['order_id' => $orderId]
            );

            return;
        }

        $orderData['status'] = $event->getNewStatus()->value;
        $orderData['updated_at'] = $event->getOccurredAt()->format('c');

        try {
            $data = $event->jsonSerialize();
            $this->logger->debug(
                'Data to index in Elasticsearch',
                ['data' => $data]
            );

            $this->readModelRepository->index($orderData);
        } catch (\Throwable $throwable) {
            $this->logger->error(
                'Error indexing order in Elasticsearch',
                [
                    'order_id' => $event->getOrderId()->toString(),
                    'error' => $throwable->getMessage(),
                    'trace' => $throwable->getTraceAsString(),
                ]
            );

            throw $throwable;
        }

        $this->logger->info(
            'Order status successfully updated in read model',
            [
                'order_id' => $orderId,
                'new_status' => $event->getNewStatus()->value,
            ]
        );
    }
}
