<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrdersList\Query;

/**
 * @see GetOrdersListQueryHandler
 */
final readonly class GetOrdersListQuery
{
    /**
     * @param array<string, string|int|null> $filters Optional filters for orders list
     */
    public function __construct(
        private array $filters = [],
        private int $page = 1,
        private int $limit = 10,
        private ?string $sortBy = null,
        private string $sortDirection = 'desc',
    ) {
    }

    /**
     * @return array<string, string|int|null>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }
}
