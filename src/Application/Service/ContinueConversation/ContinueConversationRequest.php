<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ContinueConversationRequest
{
    public function __construct(public readonly string $prompt, public readonly ConversationId $convId, public readonly string $lmName, public readonly ?HttpClientInterface $client = null)
    {
    }
}
