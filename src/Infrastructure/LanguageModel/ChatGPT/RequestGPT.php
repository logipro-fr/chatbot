<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\RequestInterface;
use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\Prompt;

class RequestGPT implements RequestInterface
{
    /**
     * @property-read string $prompt */
    public function __construct(public readonly Prompt $prompt, public readonly Context $context)
    {
    }
}
