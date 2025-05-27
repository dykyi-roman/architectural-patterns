<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use OrderContext\DomainModel\Event\DomainEventInterface;
use RuntimeException;

final readonly class OutboxPublisher implements OutboxPublisherInterface
{
    /**
     * @param OutboxEventRepository $outboxRepository
     */
    public function __construct(
        private OutboxEventRepository $outboxRepository
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function publish(DomainEventInterface $event): void
    {
        try {
            $payload = json_encode($event->toArray(), JSON_THROW_ON_ERROR);

            $outboxEvent = OutboxEvent::create(
                $event->getEventId(),
                $event->getEventName(),
                $event->getAggregateId(),
                $payload
            );

            $this->outboxRepository->save($outboxEvent);
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                sprintf('Error publishing event to outbox: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }
}
