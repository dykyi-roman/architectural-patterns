<?php

declare(strict_types=1);

namespace PaymentContext\Application\EventHandler;

use OrderContext\DomainModel\Event\OrderStatusChangedEvent;
use PaymentContext\Application\UseCases\UpdateInventory\Command\UpdateInventoryCommand;
use Psr\Log\LoggerInterface;
use Shared\Application\Service\ApplicationService;

final readonly class OrderStatusChangedEventHandler
{
    public function __construct(
        private ApplicationService $applicationService,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(OrderStatusChangedEvent $event): void
    {
        $orderId = $event->getOrderId()->toString();
        $newStatus = $event->getNewStatus();

        $this->logger->info(
            'PaymentContext: Order status change event received',
            [
                'order_id' => $orderId,
                'previous_status' => $event->getPreviousStatus()->value,
                'new_status' => $newStatus->value,
            ]
        );

        $this->applicationService->command(new UpdateInventoryCommand($event->getNewStatus()->value));
    }
}
