<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Entity\OrderItem;
use OrderContext\DomainModel\Event\OrderCreatedEvent;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use OrderContext\Infrastructure\Outbox\OutboxPublisherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

final readonly class CreateOrderCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository,
        private OutboxPublisherInterface $outboxPublisher
    ) {
    }

    /**
     * @throws \InvalidArgumentException When command is not valid
     * @throws \RuntimeException When order cannot be saved or event cannot be published
     */
    #[AsMessageHandler(bus: 'command.bus')]
    public function __invoke(CreateOrderCommand $command): void
    {
        // Create new order
        $order = Order::create($command->getCustomerId());
        
        // Add order items
        foreach ($command->getItems() as $item) {
            $orderItem = OrderItem::create(
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            
            $order->addItem($orderItem);
        }
        
        // Save order to repository
        $this->orderRepository->save($order);
        
        $this->outboxPublisher->publish(
            new OrderCreatedEvent(
                Uuid::v4()->toRfc4122(),
                new \DateTimeImmutable(),
                $order->getId(),
                $order->getCustomerId(),
                $order->getTotalAmount(),
                $this->prepareItemsForEvent($order->getItems())
            ),
        );
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
