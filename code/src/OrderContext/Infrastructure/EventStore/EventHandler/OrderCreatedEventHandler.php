<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore\EventHandler;

use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\Infrastructure\Persistence\Doctrine\Repository\ElasticsearchOrderReadModelRepository;
use Psr\Log\LoggerInterface;

final readonly class OrderCreatedEventHandler
{
    public function __construct(
        private ElasticsearchOrderReadModelRepository $readModelRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(OrderCreatedEvent $event): void
    {
        $this->logger->info(
            'Handling the order creation event to update the read model',
            ['order_id' => $event->getOrderId()->toString()]
        );

        try {
            $data = $event->jsonSerialize();
            $this->logger->debug(
                'Data to index in Elasticsearch',
                ['data' => $data]
            );

            $this->readModelRepository->index($data);
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
            'The order was successfully indexed in the read model.',
            ['order_id' => $event->getOrderId()->toString()]
        );
    }
}
