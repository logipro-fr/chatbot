<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Context\ContextMessage;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class ContextMessageType extends Type
{
    public const TYPE_NAME = 'contextmessage';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param ContextMessage $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value->getMessage();
    }

    /**
     * @param string $value
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ContextMessage
    {
        return new ContextMessage($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {

        return $platform->getStringTypeDeclarationSQL($column);
    }
}
