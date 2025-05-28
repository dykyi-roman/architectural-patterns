<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\ChangeOrderStatus\Command;

use OrderContext\DomainModel\Event\OrderStatusChangedEvent;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use Shared\DomainModel\Service\OutboxPublisherInterface;
use Shared\DomainModel\Service\TransactionServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

final readonly class ChangeOrderStatusCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository,
        private OutboxPublisherInterface $outboxPublisher,
        private TransactionServiceInterface $transactionService
    ) {
    }

    /**
     * @throws \InvalidArgumentException When command is not valid
     * @throws \DomainException When order not found or status change is invalid
     * @throws \RuntimeException When order cannot be saved or event cannot be published
     */
    #[AsMessageHandler(bus: 'command.bus')]
    public function __invoke(ChangeOrderStatusCommand $command): void
    {
        $this->transactionService->execute(function() use ($command): void {
            // Find order by id
            $order = $this->orderRepository->findById($command->getOrderId());
            if ($order === null) {
                throw new \DomainException('Order not found');
            }

            // Get current status before change
            $previousStatus = $order->getStatus();

            // Change order status
            $order->changeStatus($command->getNewStatus());
            
            // Save order in repository
            $this->orderRepository->save($order);
            
            // Create and publish domain event via outbox pattern
            $this->outboxPublisher->publish(
                new OrderStatusChangedEvent(
                    Uuid::v4()->toRfc4122(),
                    new \DateTimeImmutable(),
                    $order->getId(),
                    $previousStatus,
                    $order->getStatus()
                )
            );
        });
    }
}
