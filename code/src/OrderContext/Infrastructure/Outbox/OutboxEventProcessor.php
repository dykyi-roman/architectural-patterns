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
    public function __construct(
        private OutboxEventRepository $outboxRepository,
        private AMQPStreamConnection $amqpConnection,
        private LockFactory $lockFactory,
        private LoggerInterface $logger,
        private string $exchangeName = 'order_events'
    ) {
    }

    /**
     * Processes unprocessed events from Outbox
     *
     * The process uses locking to ensure that only one instance of the processor is
     * running at any given time, ensuring that messages are not duplicated.
     *
     * @param int $batchSize Number of events processed per call
     * @return int Number of events successfully processed
     * @throws \Exception If an unexpected error occurs
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
                'topic',
                false,
                true,
                false,
            );
            
            $successCount = 0;
            
            foreach ($events as $event) {
                try {
                    $routingKey = $this->getRoutingKeyFromEventType($event->getEventType());
                    
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
    
    private function createLock(): LockInterface
    {
        return $this->lockFactory->createLock('outbox_event_processor', 60);
    }

    /**
     * @param string $eventType Full event class name
     * @return string Routing key for RabbitMQ
     */
    private function getRoutingKeyFromEventType(string $eventType): string
    {
        // Extract the short name of the event class
        $parts = explode('\\', $eventType);
        $shortName = end($parts);
        
        // Convert to snake_case for use as routing key
        $routingKey = preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName);
        $routingKey = strtolower(str_replace('_event', '', $routingKey));
        
        return 'order.' . $routingKey;
    }
}
