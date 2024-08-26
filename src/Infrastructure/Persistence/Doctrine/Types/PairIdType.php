<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\PairId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class PairIdType extends Type
{
    public const TYPE_NAME = 'pai_id';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param PairId $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value->getId();
    }

    /**
     * @param string $value
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): PairId
    {
        return new PairId($value);
    }

    /**
     * @param mixed[] $column
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }
}
