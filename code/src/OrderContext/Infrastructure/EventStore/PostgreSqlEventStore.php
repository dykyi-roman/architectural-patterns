<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use OrderContext\DomainModel\Model\Event\DomainEvent;
use OrderContext\DomainModel\Model\Event\OrderCreatedEvent;
use OrderContext\DomainModel\Model\Event\OrderStatusChangedEvent;
use OrderContext\DomainModel\ValueObject\OrderId;
use RuntimeException;
use Shared\DomainModel\Event\DomainEventInterface;

/**
 * Реализация хранилища событий на PostgreSQL
 */
final readonly class PostgreSqlEventStore implements EventStoreInterface
{
    /**
     * @param Connection $connection Соединение с базой данных
     */
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @inheritDoc
     */
    public function append(DomainEventInterface $event): void
    {
        try {
            $this->connection->insert('event_store', [
                'event_id' => $event->getEventId(),
                'occurred_on' => $event->getOccurredOn()->format('Y-m-d H:i:s.u'),
                'event_type' => $event->getEventName(),
                'aggregate_id' => $event->getAggregateId(),
                'event_data' => json_encode($event->jsonSerialize(), JSON_THROW_ON_ERROR),
            ]);
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при сохранении события: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getEventsForAggregate(OrderId $aggregateId): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('event_store')
                ->where('aggregate_id = :aggregateId')
                ->orderBy('occurred_on', 'ASC')
                ->setParameter('aggregateId', $aggregateId->toString())
                ->executeQuery();

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->deserializeEvent($row);
            }

            return $events;
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при получении событий для агрегата: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getEventsByType(string $eventType): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('event_store')
                ->where('event_type = :eventType')
                ->orderBy('occurred_on', 'ASC')
                ->setParameter('eventType', $eventType)
                ->executeQuery();

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->deserializeEvent($row);
            }

            return $events;
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при получении событий по типу: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Десериализует событие из строки базы данных
     *
     * @param array<string, mixed> $row Строка из базы данных
     * @return DomainEvent Десериализованное событие
     * @throws RuntimeException Если тип события неизвестен
     */
    private function deserializeEvent(array $row): DomainEvent
    {
        try {
            $data = json_decode($row['event_data'], true, 512, JSON_THROW_ON_ERROR);
            
            return match ($row['event_type']) {
                OrderCreatedEvent::class => OrderCreatedEvent::fromArray($data),
                OrderStatusChangedEvent::class => OrderStatusChangedEvent::fromArray($data),
                default => throw new RuntimeException("Неизвестный тип события: {$row['event_type']}"),
            };
        } catch (\Exception $e) {
            throw new RuntimeException("Ошибка при десериализации события: {$e->getMessage()}", 0, $e);
        }
    }
}
