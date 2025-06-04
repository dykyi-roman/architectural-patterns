<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use OrderContext\DomainModel\ValueObject\CustomerId;

/**
 * Custom Doctrine type for CustomerId value object
 */
final class CustomerIdType extends Type
{
    public const NAME = 'customer_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof CustomerId) {
            throw new \InvalidArgumentException(
                sprintf('Expected %s instance, got %s', CustomerId::class, get_debug_type($value))
            );
        }

        return $value->toString();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CustomerId
    {
        if ($value === null || $value === '') {
            return null;
        }

        return CustomerId::fromString($value);
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
