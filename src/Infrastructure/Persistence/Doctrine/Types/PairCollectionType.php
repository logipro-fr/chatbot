<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\PairArray;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class PairCollectionType extends Type
{
    public const TYPE_NAME = 'pairs';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param Pair $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return serialize($value);
    }

    /**
     * @param string $value
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): PairArray
    {
        /** @var PairArray*/
        $pair = unserialize($value);

        return $pair;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
    
        return $platform->getBlobTypeDeclarationSQL($column); // Utilise la méthode de la plateforme pour obtenir la déclaration SQL pour un champ BLOB
    }
}
