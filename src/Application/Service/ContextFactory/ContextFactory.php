<?php

namespace Chatbot\Application\Service\ContextFactory;

use Chatbot\Domain\Model\Conversation\Context;

class ContextFactory
{
    public function create(string $language): Context
    {
        return new Context("respond only with the prompt translated into " . $language);
    }
}
