<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Entity\OrderItem;
use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use Shared\DomainModel\Service\OutboxPublisherInterface;
use Shared\DomainModel\Service\TransactionServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

final readonly class CreateOrderCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository,
        private OutboxPublisherInterface $outboxPublisher,
        private TransactionServiceInterface $transactionService
    ) {
    }

    /**
     * @throws \InvalidArgumentException When command is not valid
     * @throws \DomainException When order cannot be created
     * @throws \RuntimeException When order cannot be saved or event cannot be published
     */
    #[AsMessageHandler(bus: 'command.bus')]
    public function __invoke(CreateOrderCommand $command): void
    {
        $this->transactionService->execute(function() use ($command): void {
            $order = Order::create($command->orderId, $command->customerId, ...$command->getOrderItems());

            $this->orderRepository->save($order);
            
            $this->outboxPublisher->publish(
                new OrderCreatedEvent(
                    Uuid::v4()->toRfc4122(),
                    new \DateTimeImmutable(),
                    $order->getId(),
                    $order->getCustomerId(),
                    $order->calculateTotalAmount(),
                    $this->prepareItemsForEvent($order->getItems()),
                ),
            );
        });
    }
    
    /**
     * Подготавливает элементы заказа для события
     * 
     * @param array<OrderItem> $items
     * @return array<array{product_id: string, quantity: int, price: array{amount: int, currency: string}}>
     */
    private function prepareItemsForEvent(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'product_id' => $item->getProductId()->toString(),
                'quantity' => $item->getQuantity(),
                'price' => [
                    'amount' => $item->getPrice()->getAmount(),
                    'currency' => $item->getPrice()->getCurrency(),
                ],
            ];
        }
        return $result;
    }
}
