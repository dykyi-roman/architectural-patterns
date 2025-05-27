<?php

declare(strict_types=1);

namespace OrderContext\Presentation\Api\Response;

/**
 * DTO for representing order response
 */
final readonly class OrderResponse implements \JsonSerializable
{
    /**
     * @param string $id Order identifier
     * @param string $customerId Customer identifier
     * @param string $status Order status
     * @param array<string, mixed> $totalAmount Total order amount
     * @param array<array<string, mixed>> $items Order items
     * @param string $createdAt Order creation datetime
     * @param string|null $updatedAt Order last update datetime
     */
    public function __construct(
        public string $id,
        public string $customerId,
        public string $status,
        public array $totalAmount,
        public array $items,
        public string $createdAt,
        public ?string $updatedAt = null
    ) {
    }

    /**
     * Creates DTO from read model data
     *
     * @param array<string, mixed> $data Order data from read model
     * @return self
     */
    public static function fromReadModel(array $data): self
    {
        return new self(
            id: $data['id'],
            customerId: $data['customer_id'],
            status: $data['status'],
            totalAmount: $data['total_amount'] ?? [],
            items: $data['items'] ?? [],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'] ?? null
        );
    }

    /**
     * Serializes the object to a value that can be serialized by json_encode()
     *
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
