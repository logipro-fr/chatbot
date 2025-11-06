<?php

namespace Chatbot\Application\Service\EditContext;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;

class EditContextResponse
{
    public function __construct(
        public readonly ContextId $contextId,
        public readonly ContextMessage $contextMessage,
    ) {
    }
}
