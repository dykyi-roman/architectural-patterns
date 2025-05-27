<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\CreateOrder\Command;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Entity\OrderItem;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class CreateOrderCommandHandler
{
    public function __construct(
        private OrderWriteModelRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @throws \InvalidArgumentException When command is not valid
     */
    #[AsMessageHandler(bus: 'command.bus')]
    public function __invoke(CreateOrderCommand $command): void
    {
        // Создаем новый заказ
        $order = Order::create($command->getCustomerId());
        
        // Добавляем элементы заказа
        foreach ($command->getItems() as $item) {
            $orderItem = OrderItem::create(
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            
            $order->addItem($orderItem);
        }
        
        $this->orderRepository->save($order);
    }
}
