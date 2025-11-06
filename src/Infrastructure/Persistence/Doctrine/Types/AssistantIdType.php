<?php

namespace Chatbot\Infrastructure\Persistence\Doctrine\Types;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class AssistantIdType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?AssistantId
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string value for AssistantId');
        }

        return new AssistantId($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof AssistantId) {
            return $value->getId();
        }

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected AssistantId or string value');
        }

        return $value;
    }

    public function getName(): string
    {
        return 'ast_id';
    }
}
