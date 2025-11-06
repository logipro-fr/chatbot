<?php

namespace Chatbot\Application\Service\ContextFactory;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextMessage;

class ContextFactory
{
    public function create(string $language): Context
    {
        return new Context(new ContextMessage("respond only with the prompt translated into " . $language));
    }
}
