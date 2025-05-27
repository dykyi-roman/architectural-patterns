<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

use InvalidArgumentException;
use Stringable;

/**
 * Value Object для идентификатора клиента
 */
final readonly class CustomerId implements Stringable
{
    /**
     * @param string $value Значение идентификатора клиента
     * @throws InvalidArgumentException Если идентификатор пустой
     */
    private function __construct(
        private string $value
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('CustomerId не может быть пустым');
        }
    }

    /**
     * Создает новый идентификатор клиента из строки
     *
     * @param string $value Строковое представление идентификатора
     * @return self
     * @throws InvalidArgumentException Если идентификатор пустой
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Возвращает строковое представление идентификатора
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Преобразует объект в строку
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Проверяет равенство с другим идентификатором
     *
     * @param self $other Другой идентификатор для сравнения
     * @return bool
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
