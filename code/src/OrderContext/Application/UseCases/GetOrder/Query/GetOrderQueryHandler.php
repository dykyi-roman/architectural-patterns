<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrder\Query;

use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;

final readonly class GetOrderQueryHandler
{
    public function __construct(
        private OrderReadModelRepositoryInterface $orderReadModelRepository,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke(GetOrderQuery $query): ?array
    {
        return $this->orderReadModelRepository->findById($query->getOrderId());
    }
}
