<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrdersList\Query;

use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final readonly class GetOrdersListQueryHandler
{
    public function __construct(
        private OrderReadModelRepositoryInterface $orderReadModelRepository
    ) {
    }

    /**
     * Handles the get orders list query
     *
     * @return array{items: array<array<string, mixed>>, total: int, page: int, limit: int} Orders list with pagination
     * @throws \InvalidArgumentException When query is not valid
     */
    #[AsMessageHandler]
    public function __invoke(GetOrdersListQuery $query): array
    {
        // Get orders list with pagination from read model repository
        $result = $this->orderReadModelRepository->findAll(
            $query->getFilters(),
            $query->getPage(),
            $query->getLimit(),
            $query->getSortBy(),
            $query->getSortDirection()
        );

        // Return response with pagination
        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->getPage(),
            'limit' => $query->getLimit(),
        ];
    }
}
