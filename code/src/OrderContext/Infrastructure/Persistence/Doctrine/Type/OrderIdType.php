<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use OrderContext\DomainModel\ValueObject\OrderId;

/**
 * Custom Doctrine type for OrderId value object
 */
final class OrderIdType extends Type
{
    public const NAME = 'order_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof OrderId) {
            throw new \InvalidArgumentException(
                sprintf('Expected %s instance, got %s', OrderId::class, get_debug_type($value))
            );
        }

        return $value->toString();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?OrderId
    {
        if ($value === null || $value === '') {
            return null;
        }

        return OrderId::fromString($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
