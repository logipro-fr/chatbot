<?php

namespace Chatbot\Application\Service\DeleteContext;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;

class DeleteContextResponse
{
    public function __construct(
        public readonly string $message,
    )
    {
    }
}
