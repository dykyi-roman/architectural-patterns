<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\SaveOrderException;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class CreateOrderCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @throws SaveOrderException When order cannot be saved or event cannot be published
     * @throws \RuntimeException When order event cannot be published
     * @throws \Throwable
     */
    #[AsMessageHandler(bus: 'command.bus')]
    public function __invoke(CreateOrderCommand $command): void
    {
        $order = Order::create($command->orderId, $command->customerId, ...$command->getOrderItems());

        $this->orderRepository->save($order, true, true);
    }
}
