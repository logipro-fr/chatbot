<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Prompt;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class PromptType extends Type
{
    public const TYPE_NAME = 'prompt';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param Prompt $value
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value->getUserResquest();
    }

    /**
     * @param string $value
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): Prompt
    {
        return new Prompt($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {

        return 'TEXT';
    }
}
