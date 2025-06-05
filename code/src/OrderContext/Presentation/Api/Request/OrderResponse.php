<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Request;

final readonly class OrderResponse implements \JsonSerializable
{
    /**
     * @param array<string, mixed>        $totalAmount Total order amount
     * @param array<array<string, mixed>> $items       Order items
     * @param string|null                 $updatedAt   Order last update datetime
     */
    public function __construct(
        private string $id,
        private string $customerId,
        private string $status,
        private array $totalAmount,
        private array $items,
        private string $createdAt,
        private ?string $updatedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromReadModel(array $data): self
    {
        return new self(
            $data['id'],
            $data['customer_id'],
            $data['status'],
            $data['total_amount'] ?? [],
            $data['items'] ?? [],
            $data['created_at'],
            $data['updated_at'] ?? null
        );
    }

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
