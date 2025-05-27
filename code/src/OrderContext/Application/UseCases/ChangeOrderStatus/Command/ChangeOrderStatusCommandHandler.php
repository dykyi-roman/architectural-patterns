<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\ChangeOrderStatus\Command;

use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class ChangeOrderStatusCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @throws \InvalidArgumentException When command is not valid
     * @throws \DomainException When order not found or status change is invalid
     */
    #[AsMessageHandler]
    public function __invoke(ChangeOrderStatusCommand $command): void
    {
        // Find order by id
        $order = $this->orderRepository->findById($command->getOrderId());
        if ($order === null) {
            throw new \DomainException('Order not found');
        }

        // Change order status
        $order->changeStatus($command->getNewStatus());
        
        // Save order in repository
        $this->orderRepository->save($order);
    }
}
