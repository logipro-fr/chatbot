<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\RequestInterface;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\Prompt;

class RequestGPT implements RequestInterface
{
    /**
     * @property-read string $prompt */
    public function __construct(
        public readonly Prompt $prompt,
        public readonly Context $context,
        public readonly Conversation $conversation
    ) {
    }
}
