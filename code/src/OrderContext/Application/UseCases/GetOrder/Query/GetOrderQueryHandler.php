<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrder\Query;

use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class GetOrderQueryHandler
{
    public function __construct(
        private OrderReadModelRepositoryInterface $orderReadModelRepository
    ) {
    }

    /**
     * @return array<string, mixed>|null Order data or null if not found
     * @throws \InvalidArgumentException When query is not valid
     */
    #[AsMessageHandler(bus: 'query.bus')]
    public function __invoke(GetOrderQuery $query): ?array
    {
        return $this->orderReadModelRepository->findById($query->getOrderId());
    }
}
