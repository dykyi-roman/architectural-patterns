<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class ProductId implements \Stringable
{
    /**
     * @throws \InvalidArgumentException
     */
    #[ORM\Column(name: 'product_id', type: 'string', length: 36)]
    private function __construct(
        private string $value,
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('ProductId cannot be empty');
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
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
