<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\Enum;

enum OrderStatus: string implements \JsonSerializable
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

    /**
     * @return array<string>
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
