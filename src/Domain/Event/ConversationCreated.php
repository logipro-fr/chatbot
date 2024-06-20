<?php

namespace Chatbot\Domain\Event;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Phariscope\Event\Psr14\Event;

class ConversationCreated extends Event
{
    public function __construct(public readonly ConversationId $conversationId)
    {
        parent::__construct();
    }
}
