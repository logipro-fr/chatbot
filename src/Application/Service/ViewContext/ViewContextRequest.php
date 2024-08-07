<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Domain\Model\Context\ContextId;

class ViewContextRequest
{
    public function __construct(
        public readonly ContextId $contextId,
    ) {
    }
}
