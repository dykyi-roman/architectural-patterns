<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Money implements \JsonSerializable
{
    /**
     * @throws \InvalidArgumentException
     */
    #[ORM\Column(name: 'amount', type: 'integer')]
    private function __construct(
        private int $amount,

        #[ORM\Column(name: 'currency', type: 'string', length: 3)]
        private string $currency,
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('The amount cannot be negative.');
        }

        if (empty($currency)) {
            throw new \InvalidArgumentException('Currency must be specified');
        }

        if (3 !== strlen($currency)) {
            throw new \InvalidArgumentException('The currency code must be 3 characters long (ISO 4217)');
        }
    }

    public static function fromAmount(int|float $amount, string $currency): self
    {
        if (is_float($amount)) {
            $amountInMinor = (int) round($amount * 100);

            return new self($amountInMinor, strtoupper($currency));
        }

        return new self($amount, strtoupper($currency));
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getAmountAsFloat(): float
    {
        return $this->amount / 100;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("You cannot add amounts in different currencies: {$this->currency} и {$other->currency}");
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("You cannot subtract amounts in different currencies: {$this->currency} и {$other->currency}");
        }

        $result = $this->amount - $other->amount;

        if ($result < 0) {
            throw new \InvalidArgumentException('Результат вычитания не может быть отрицательным');
        }

        return new self($result, $this->currency);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('The multiplier cannot be negative.');
        }

        $result = (int) round($this->amount * $multiplier);

        return new self($result, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function greaterThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("You cannot compare amounts in different currencies: {$this->currency} и {$other->currency}");
        }

        return $this->amount > $other->amount;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function lessThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException("You cannot compare amounts in different currencies: {$this->currency} и {$other->currency}");
        }

        return $this->amount < $other->amount;
    }

    public function toString(): string
    {
        $amountAsFloat = $this->getAmountAsFloat();

        return sprintf('%.2f %s', $amountAsFloat, $this->currency);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
