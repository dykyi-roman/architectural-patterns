<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

use Shared\Presentation\Responder\ResponderInterface;

final readonly class GetOrdersListResponse implements ResponderInterface
{
    /**
     * @param array<array<string, mixed>> $items
     * @param int $total
     * @param int $page
     * @param int $limit
     */
    public function __construct(
        private array $items,
        private int $total,
        private int $page,
        private int $limit,
    ) {
    }

    public function respond(): ResponderInterface
    {
        return $this;
    }

    /**
     * @return array{items: array<array<string, mixed>>, total: int, page: int, limit: int}
     */
    public function payload(): array
    {
        return [
            'items' => array_map(
                fn (array $orderData) => OrderResponse::fromReadModel($orderData)->jsonSerialize(),
                $this->items
            ),
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }

    public function statusCode(): int
    {
        return 200;
    }

    public function headers(): array
    {
        return ['Content-Type' => 'application/json'];
    }
}
