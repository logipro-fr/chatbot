<?php

namespace Chatbot\Application\Service;

use Chatbot\Domain\Model\Conversation\Conversation;

interface ApiInterface
{
    public function ConversationApiRequest(Conversation $conversation): Response;
}
