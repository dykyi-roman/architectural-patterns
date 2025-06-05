<?php

declare(strict_types=1);

namespace OrderContext\Infrastructure\Persistence\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use OrderContext\DomainModel\ValueObject\Money;

final class MoneyType extends Type
{
    public const string NAME = 'money';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new \InvalidArgumentException(sprintf('Expected %s instance, got %s', Money::class, get_debug_type($value)));
        }

        return json_encode([
            'amount' => $value->getAmount(),
            'currency' => $value->getCurrency(),
        ]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Money
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $data = json_decode($value, true);

        if (!is_array($data) || !isset($data['amount']) || !isset($data['currency'])) {
            throw new \InvalidArgumentException('Invalid money data format');
        }

        return Money::fromAmount($data['amount'], $data['currency']);
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
