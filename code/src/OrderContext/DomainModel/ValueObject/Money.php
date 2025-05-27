<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

use InvalidArgumentException;

/**
 * Value Object для представления денежной суммы
 */
final readonly class Money
{
    /**
     * @param int $amount Сумма в минимальных единицах валюты (копейках, центах и т.д.)
     * @param string $currency Код валюты (ISO 4217)
     * @throws InvalidArgumentException Если сумма отрицательная или валюта не указана
     */
    private function __construct(
        private int $amount,
        private string $currency
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Сумма не может быть отрицательной');
        }

        if (empty($currency)) {
            throw new InvalidArgumentException('Валюта должна быть указана');
        }

        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Код валюты должен состоять из 3 символов (ISO 4217)');
        }
    }

    /**
     * Создает объект Money из суммы и валюты
     *
     * @param int|float $amount Сумма в минимальных единицах валюты
     * @param string $currency Код валюты
     * @return self
     */
    public static function fromAmount(int|float $amount, string $currency): self
    {
        if (is_float($amount)) {
            $amountInMinor = (int)round($amount * 100);

            return new self($amountInMinor, strtoupper($currency));
        }

        return new self($amount, strtoupper($currency));
    }

    /**
     * Возвращает сумму в минимальных единицах валюты
     *
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * Возвращает сумму в основных единицах валюты (например, рублях)
     *
     * @return float
     */
    public function getAmountAsFloat(): float
    {
        return $this->amount / 100;
    }

    /**
     * Возвращает код валюты
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Прибавляет другую денежную сумму
     *
     * @param self $other Другая денежная сумма
     * @return self Новый объект с результатом
     * @throws InvalidArgumentException Если валюты не совпадают
     */
    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Нельзя складывать суммы в разных валютах: {$this->currency} и {$other->currency}"
            );
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Вычитает другую денежную сумму
     *
     * @param self $other Другая денежная сумма
     * @return self Новый объект с результатом
     * @throws InvalidArgumentException Если валюты не совпадают или результат отрицательный
     */
    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Нельзя вычитать суммы в разных валютах: {$this->currency} и {$other->currency}"
            );
        }

        $result = $this->amount - $other->amount;

        if ($result < 0) {
            throw new InvalidArgumentException('Результат вычитания не может быть отрицательным');
        }

        return new self($result, $this->currency);
    }

    /**
     * Умножает сумму на коэффициент
     *
     * @param float $multiplier Множитель
     * @return self Новый объект с результатом
     * @throws InvalidArgumentException Если множитель отрицательный
     */
    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Множитель не может быть отрицательным');
        }

        $result = (int)round($this->amount * $multiplier);
        return new self($result, $this->currency);
    }

    /**
     * Проверяет равенство с другой денежной суммой
     *
     * @param self $other Другая денежная сумма
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    /**
     * Проверяет, больше ли текущая сумма, чем другая
     *
     * @param self $other Другая денежная сумма
     * @return bool
     * @throws InvalidArgumentException Если валюты не совпадают
     */
    public function greaterThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Нельзя сравнивать суммы в разных валютах: {$this->currency} и {$other->currency}"
            );
        }

        return $this->amount > $other->amount;
    }

    /**
     * Проверяет, меньше ли текущая сумма, чем другая
     *
     * @param self $other Другая денежная сумма
     * @return bool
     * @throws InvalidArgumentException Если валюты не совпадают
     */
    public function lessThan(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Нельзя сравнивать суммы в разных валютах: {$this->currency} и {$other->currency}"
            );
        }

        return $this->amount < $other->amount;
    }

    /**
     * Возвращает строковое представление денежной суммы
     *
     * @return string
     */
    public function toString(): string
    {
        $amountAsFloat = $this->getAmountAsFloat();
        return sprintf('%.2f %s', $amountAsFloat, $this->currency);
    }
}
