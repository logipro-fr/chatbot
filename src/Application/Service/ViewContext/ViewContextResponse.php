<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Domain\Model\Context\ContextMessage;

class ViewContextResponse
{
    public function __construct(
        public readonly ContextMessage $contextMessage,
    ) {
    }
}
