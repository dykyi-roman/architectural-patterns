<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use OrderContext\DomainModel\Event\DomainEventInterface;
use RuntimeException;

/**
 * Реализация публикатора сообщений через паттерн Outbox
 */
final readonly class OutboxPublisher implements OutboxPublisherInterface
{
    /**
     * @param OutboxEventRepository $outboxRepository Репозиторий для работы с outbox событиями
     */
    public function __construct(
        private OutboxEventRepository $outboxRepository
    ) {
    }

    /**
     * @inheritDoc
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
        } catch (\Exception $e) {
            throw new RuntimeException("Ошибка при публикации события в outbox: {$e->getMessage()}", 0, $e);
        }
    }
}
