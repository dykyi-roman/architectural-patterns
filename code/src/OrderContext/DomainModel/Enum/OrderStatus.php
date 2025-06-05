<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Enum;

enum OrderStatus: string
{
    case CREATED = 'created';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case PROCESSING = 'processing';

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $status): self
    {
        return match (strtolower($status)) {
            'created' => self::CREATED,
            'paid' => self::PAID,
            'processing' => self::PROCESSING,
            'cancelled' => self::CANCELLED,
            default => throw new \InvalidArgumentException("Invalid order status: {$status}"),
        };
    }

    public function isCreated(): bool
    {
        return $this === self::CREATED;
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function canBePaid(): bool
    {
        return $this === self::CREATED;
    }

    public function canBeCancelled(): bool
    {
        return $this === self::CREATED;
    }

    /**
     * @return array<string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
