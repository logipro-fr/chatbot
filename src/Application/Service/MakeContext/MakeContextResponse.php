<?php

namespace Chatbot\Application\Service\MakeContext;

use Chatbot\Domain\Model\Context\ContextId;

class MakeContextResponse
{
    public function __construct(
        public readonly string $contextId,
    ) {
    }
}
