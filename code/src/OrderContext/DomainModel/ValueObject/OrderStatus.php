<?php

declare(strict_types=1);

namespace OrderContext\DomainModel\ValueObject;

use InvalidArgumentException;

/**
 * Enum для статуса заказа
 */
enum OrderStatus: string
{
    case CREATED = 'created';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    
    /**
     * Создает статус из строкового представления
     *
     * @param string $status Строковое представление статуса
     * @return self
     * @throws InvalidArgumentException Если статус недопустимый
     */
    public static function fromString(string $status): self
    {
        return match (strtolower($status)) {
            'created' => self::CREATED,
            'paid' => self::PAID,
            'cancelled' => self::CANCELLED,
            default => throw new InvalidArgumentException("Недопустимый статус заказа: {$status}")
        };
    }
    
    /**
     * Проверяет, является ли статус "Создан"
     *
     * @return bool
     */
    public function isCreated(): bool
    {
        return $this === self::CREATED;
    }
    
    /**
     * Проверяет, является ли статус "Оплачен"
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }
    
    /**
     * Проверяет, является ли статус "Отменен"
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
    
    /**
     * Возможен ли переход в статус "Оплачен" из текущего статуса
     *
     * @return bool
     */
    public function canBePaid(): bool
    {
        return $this === self::CREATED;
    }
    
    /**
     * Возможен ли переход в статус "Отменен" из текущего статуса
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return $this === self::CREATED;
    }
}
