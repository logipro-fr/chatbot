<?php

namespace Chatbot\Application\Service\EditContext;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;

class EditContextRequest
{
    public function __construct(
        public readonly ContextMessage $message,
        public readonly ContextId $id
    ) {
    }
}
