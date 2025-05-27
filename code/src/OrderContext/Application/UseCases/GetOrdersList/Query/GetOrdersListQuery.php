<?php

declare(strict_types=1);

namespace OrderContext\Application\UseCases\GetOrdersList\Query;

/**
 * @see \OrderContext\Application\UseCases\GetOrdersList\Query\GetOrdersListQueryHandler
 */
final readonly class GetOrdersListQuery
{
    /**
     * @param array<string, string|int|null> $filters Optional filters for orders list
     * @param int $page Page number for pagination
     * @param int $limit Items per page
     * @param string|null $sortBy Field to sort by
     * @param string $sortDirection Sort direction (asc or desc)
     */
    public function __construct(
        private array $filters = [],
        private int $page = 1,
        private int $limit = 10,
        private ?string $sortBy = null,
        private string $sortDirection = 'desc'
    ) {
    }

    /**
     * Returns filters
     * 
     * @return array<string, string|int|null>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns page number
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Returns items per page
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Returns field to sort by
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * Returns sort direction
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }
}
