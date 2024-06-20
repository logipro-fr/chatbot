<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Conversation\LanguageModelInterface;

abstract class LanguageModelAbstractFactory
{
    abstract public function create(string $lmName, string $context): LanguageModelInterface;
}
