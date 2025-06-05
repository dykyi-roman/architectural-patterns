<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrdersList\Query;

use OrderContext\DomainModel\Repository\OrderReadModelRepositoryInterface;

final readonly class GetOrdersListQueryHandler
{
    public function __construct(
        private OrderReadModelRepositoryInterface $orderReadModelRepository,
    ) {
    }

    /**
     * @return array{items: array<array<string, mixed>>, total: int, page: int, limit: int} Orders list with pagination
     *
     * @throws \InvalidArgumentException When query is not valid
     */
    public function __invoke(GetOrdersListQuery $query): array
    {
        $result = $this->orderReadModelRepository->findAll(
            $query->getFilters(),
            $query->getPage(),
            $query->getLimit(),
            $query->getSortBy(),
            $query->getSortDirection()
        );

        return [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->getPage(),
            'limit' => $query->getLimit(),
        ];
    }
}
