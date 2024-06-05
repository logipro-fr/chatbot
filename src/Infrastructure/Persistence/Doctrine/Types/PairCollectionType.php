<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Pair;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PairCollectionType extends Type {

    public const TYPE_NAME = 'pairs';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param array<Pair> $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return serialize($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): Pair
    {
        /** @var Pair*/
        $pair = unserialize($value);
        return $pair;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return "text";
    }

}