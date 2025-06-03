<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\ChangeOrderStatus\Command;

use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use Shared\DomainModel\Service\TransactionServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class ChangeOrderStatusCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository,
        private TransactionServiceInterface $transactionService,
    ) {
    }

    /**
     * @throws \Throwable
     */
    #[AsMessageHandler(bus: 'command.bus')]
    public function __invoke(ChangeOrderStatusCommand $command): void
    {
        $this->transactionService->execute(function() use ($command): void {
            $order = $this->orderRepository->findById($command->getOrderId());
            if ($order === null) {
                throw new \DomainException('Order not found');
            }

            $order->changeStatus($command->getNewStatus());
            $this->orderRepository->save($order, true);
        });
    }
}
