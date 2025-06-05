<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

enum OrderStatus: string
{
    case CREATED = 'created';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $status): self
    {
        return match (strtolower($status)) {
            'created' => self::CREATED,
            'paid' => self::PAID,
            'cancelled' => self::CANCELLED,
            default => throw new \InvalidArgumentException("Invalid order status: {$status}"),
        };
    }

    public function isCreated(): bool
    {
        return self::CREATED === $this;
    }

    public function isPaid(): bool
    {
        return self::PAID === $this;
    }

    public function isCancelled(): bool
    {
        return self::CANCELLED === $this;
    }

    public function canBePaid(): bool
    {
        return self::CREATED === $this;
    }

    public function canBeCancelled(): bool
    {
        return self::CREATED === $this;
    }
}
