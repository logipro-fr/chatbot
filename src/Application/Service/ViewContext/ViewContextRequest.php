<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Domain\Model\Context\ContextId;

class ViewContextRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $type
    ) {
    }
}
