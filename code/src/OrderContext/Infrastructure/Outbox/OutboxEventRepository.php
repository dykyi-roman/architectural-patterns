<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use RuntimeException;

final readonly class OutboxEventRepository
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * Сохраняет событие в таблицу outbox
     *
     * @param OutboxEvent $event Событие для сохранения
     * @return void
     * @throws RuntimeException При ошибке сохранения
     */
    public function save(OutboxEvent $event): void
    {
        try {
            $this->connection->insert('outbox_events', [
                'id' => $event->getId(),
                'event_id' => $event->getEventId(),
                'event_type' => $event->getEventType(),
                'aggregate_id' => $event->getAggregateId(),
                'payload' => $event->getPayload(),
                'created_at' => $event->getCreatedAt()->format('Y-m-d H:i:s.u'),
                'processed_at' => $event->getProcessedAt()?->format('Y-m-d H:i:s.u'),
                'is_processed' => $event->isProcessed(),
                'retry_count' => $event->getRetryCount(),
                'error' => $event->getError(),
            ]);
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при сохранении события в outbox: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Обновляет событие в таблице outbox
     *
     * @param OutboxEvent $event Событие для обновления
     * @return void
     * @throws RuntimeException При ошибке обновления
     */
    public function update(OutboxEvent $event): void
    {
        try {
            $this->connection->update(
                'outbox_events',
                [
                    'processed_at' => $event->getProcessedAt()?->format('Y-m-d H:i:s.u'),
                    'is_processed' => $event->isProcessed(),
                    'retry_count' => $event->getRetryCount(),
                    'error' => $event->getError(),
                ],
                ['id' => $event->getId()]
            );
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при обновлении события в outbox: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Получает список необработанных событий
     *
     * @param int $limit Максимальное количество событий
     * @return array<OutboxEvent> Массив необработанных событий
     * @throws RuntimeException При ошибке чтения
     */
    public function findUnprocessed(int $limit = 100): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select('*')
                ->from('outbox_events')
                ->where('is_processed = :isProcessed')
                ->orderBy('created_at', 'ASC')
                ->setParameter('isProcessed', false)
                ->setMaxResults($limit)
                ->executeQuery();

            $events = [];
            while ($row = $stmt->fetchAssociative()) {
                $events[] = $this->hydrateOutboxEvent($row);
            }

            return $events;
        } catch (Exception $e) {
            throw new RuntimeException("Ошибка при получении необработанных событий: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Создает объект OutboxEvent из данных БД
     *
     * @param array<string, mixed> $data Данные из БД
     * @return OutboxEvent
     */
    private function hydrateOutboxEvent(array $data): OutboxEvent
    {
        return new OutboxEvent(
            $data['id'],
            $data['event_id'],
            $data['event_type'],
            $data['aggregate_id'],
            $data['payload'],
            new \DateTimeImmutable($data['created_at']),
            isset($data['processed_at']) ? new \DateTimeImmutable($data['processed_at']) : null,
            (bool)$data['is_processed'],
            (int)$data['retry_count'],
            $data['error']
        );
    }
}
