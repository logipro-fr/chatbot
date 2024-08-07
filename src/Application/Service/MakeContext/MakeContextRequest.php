<?php

namespace Chatbot\Application\Service\MakeContext;

use Chatbot\Domain\Model\Context\ContextMessage;

class MakeContextRequest
{
    public function __construct(
        public readonly ContextMessage $message,
    ) {
    }
}
