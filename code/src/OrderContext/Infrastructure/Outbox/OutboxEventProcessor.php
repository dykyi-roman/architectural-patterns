<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Outbox;

use Psr\Log\LoggerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

final readonly class OutboxEventProcessor
{
    /**
     * @param string $exchangeName Имя обменника в RabbitMQ
     */
    public function __construct(
        private OutboxEventRepository $outboxRepository,
        private AMQPStreamConnection $amqpConnection,
        private LockFactory $lockFactory,
        private LoggerInterface $logger,
        private string $exchangeName = 'order_events'
    ) {
    }

    /**
     * Обрабатывает необработанные события из Outbox
     * 
     * Процесс использует блокировку для обеспечения того, что только один экземпляр процессора
     * работает в любой момент времени, что гарантирует отсутствие дублирования сообщений.
     *
     * @param int $batchSize Количество событий, обрабатываемых за один вызов
     * @return int Количество успешно обработанных событий
     * @throws \Exception При непредвиденной ошибке
     */
    public function processOutboxEvents(int $batchSize = 100): int
    {
        $lock = $this->createLock();
        
        if (!$lock->acquire()) {
            $this->logger->info('Outbox processor is already running');
            return 0;
        }
        
        try {
            $events = $this->outboxRepository->findUnprocessed($batchSize);
            if (empty($events)) {
                $this->logger->debug('No unprocessed outbox events found');
                return 0;
            }
            
            $this->logger->info(sprintf('Found %d unprocessed outbox events', count($events)));
            
            $channel = $this->amqpConnection->channel();
            $channel->exchange_declare(
                $this->exchangeName, 
                'topic',     // тип обменника
                false,      // passive
                true,       // durable (сохраняется при перезагрузке брокера)
                false       // auto_delete
            );
            
            $successCount = 0;
            
            foreach ($events as $event) {
                try {
                    // Определяем routing key на основе типа события
                    $routingKey = $this->getRoutingKeyFromEventType($event->getEventType());
                    
                    // Публикуем сообщение в RabbitMQ
                    $message = new AMQPMessage(
                        $event->getPayload(),
                        [
                            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                            'content_type' => 'application/json',
                            'message_id' => $event->getId(),
                            'timestamp' => time(),
                            'type' => $event->getEventType(),
                        ]
                    );
                    
                    $channel->basic_publish($message, $this->exchangeName, $routingKey);
                    
                    // Помечаем событие как обработанное
                    $event->markAsProcessed();
                    $this->outboxRepository->update($event);
                    
                    $successCount++;
                    
                    $this->logger->info(sprintf(
                        'Successfully published outbox event %s of type %s to RabbitMQ',
                        $event->getId(),
                        $event->getEventType()
                    ));
                } catch (\Exception $e) {
                    $this->logger->error(sprintf(
                        'Error publishing outbox event %s: %s',
                        $event->getId(),
                        $e->getMessage()
                    ));
                    
                    $event->increaseRetryCount($e->getMessage());
                    $this->outboxRepository->update($event);
                }
            }
            
            $channel->close();
            
            return $successCount;
        } finally {
            $lock->release();
        }
    }
    
    /**
     * Создает объект блокировки для защиты от параллельного выполнения
     *
     * @return LockInterface
     */
    private function createLock(): LockInterface
    {
        return $this->lockFactory->createLock('outbox_event_processor', 60);
    }
    
    /**
     * Определяет routing key на основе типа события
     *
     * @param string $eventType Полное имя класса события
     * @return string Routing key для RabbitMQ
     */
    private function getRoutingKeyFromEventType(string $eventType): string
    {
        // Извлекаем короткое имя класса события
        $parts = explode('\\', $eventType);
        $shortName = end($parts);
        
        // Преобразуем в snake_case для использования в качестве routing key
        $routingKey = preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName);
        $routingKey = strtolower(str_replace('_event', '', $routingKey));
        
        return 'order.' . $routingKey;
    }
}
