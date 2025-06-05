<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

final readonly class OrderResponse implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $totalAmount
     * @param array<array<string, mixed>> $items
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $status,
        public array $totalAmount,
        public array $items,
        public string $createdAt,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromReadModel(array $data): self
    {
        return new self(
            id: $data['order_id'] ?? $data['aggregate_id'] ?? '',
            customerId: $data['customer_id'] ?? '',
            status: $data['status'] ?? 'created',
            totalAmount: $data['total_amount'] ?? [],
            items: $data['items'] ?? [],
            createdAt: $data['occurred_on'] ?? $data['created_at'] ?? date('c'),
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'status' => $this->status,
            'total_amount' => $this->totalAmount,
            'items' => $this->items,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
