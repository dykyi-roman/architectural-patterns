<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Repository;

use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\OrderNotFoundException;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use OrderContext\DomainModel\ValueObject\OrderId;
use RuntimeException;
use Shared\Infrastructure\Persistence\Doctrine\Repository\AbstractDoctrineRepository;

final readonly class DoctrineOrderRepository extends AbstractDoctrineRepository implements
    OrderWriteModelRepositoryInterface
{
    public function exists(OrderId $orderId): bool
    {
        try {
            $count = $this->repository->count(['id' => $orderId->toString()]);

            return $count > 0;
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                "Ошибка при проверке существования заказа: {$exception->getMessage()}",
                0,
                $exception
            );
        }
    }

    /**
     * @throws OrderNotFoundException
     */
    public function findById(OrderId $orderId): Order
    {
        $order = $this->repository->find($orderId->toString());

        return $order ?? throw new OrderNotFoundException($orderId);
    }

    protected function entityClass(): string
    {
        return Order::class;
    }
}
