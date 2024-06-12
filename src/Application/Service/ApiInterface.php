<?php

namespace Chatbot\Application\Service;

use Chatbot\Domain\Model\Conversation\Conversation;

interface ApiInterface
{
    public function conversationApiRequest(Conversation $conversation): Response;
}
