<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Conversation\Answer;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class AnswerType extends Type
{
    public const TYPE_NAME = 'answer';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param Answer $value
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
    public function convertToPHPValue($value, AbstractPlatform $platform): Answer
    {
        return new Answer($value, 200);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {

        return 'TEXT';
    }
}
