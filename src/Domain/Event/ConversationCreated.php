<?php

namespace Chatbot\Domain\Event;

use Phariscope\Event\Psr14\Event;

class ConversationCreated extends Event
{
    public function __construct(public readonly string $conversationId)
    {
        parent::__construct();
    }
}
