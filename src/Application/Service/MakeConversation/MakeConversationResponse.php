<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\PairArray;

class MakeConversationResponse
{
    public function __construct(
        public readonly ConversationId $conversationId,
        public readonly Pair $pair ,
        public readonly int $nbPair 
    ) {
    }
}
