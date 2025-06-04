<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

use InvalidArgumentException;
use Stringable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class OrderId implements Stringable
{
    #[ORM\Column(name: 'id', type: 'string', length: 36)]
    private function __construct(
        private string $value
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('OrderId не может быть пустым');
        }
    }

    public static function generate(): self
    {
        return new self(uuid_create());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
