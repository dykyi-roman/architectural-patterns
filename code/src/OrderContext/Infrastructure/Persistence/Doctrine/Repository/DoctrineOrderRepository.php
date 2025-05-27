<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use OrderContext\DomainModel\Entity\Order;
use OrderContext\DomainModel\Exception\OrderNotFoundException;
use OrderContext\DomainModel\Repository\OrderWriteModelRepositoryInterface;
use OrderContext\DomainModel\ValueObject\OrderId;
use RuntimeException;

final readonly class DoctrineOrderRepository implements OrderWriteModelRepositoryInterface
{
    /** @var EntityRepository<Order> */
    private EntityRepository $repository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->repository = $this->entityManager->getRepository(Order::class);
    }

    public function save(Order $order): void
    {
        try {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            throw new RuntimeException("Ошибка при сохранении заказа: {$exception->getMessage()}", 0, $exception);
        }
    }

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
     * @throw OrderNotFoundException
     */
    public function findById(OrderId $orderId): Order
    {
        $order = $this->repository->find($orderId->toString());

        return $order ?? throw new OrderNotFoundException($orderId);
    }
}
