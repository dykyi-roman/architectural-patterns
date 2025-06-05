<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use OrderContext\DomainModel\ValueObject\ProductId;

final class ProductIdType extends Type
{
    public const string NAME = 'product_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ProductId) {
            throw new \InvalidArgumentException(sprintf('Expected %s instance, got %s', ProductId::class, get_debug_type($value)));
        }

        return $value->toString();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?ProductId
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return ProductId::fromString($value);
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
