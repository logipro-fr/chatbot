<?php

namespace Chatbot\Tests\Application\Service\ContinueAssistantConversation;

use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationRequest;
use Chatbot\Domain\Model\Conversation\ConversationId;
use PHPUnit\Framework\TestCase;

class ContinueAssistantConversationRequestTest extends TestCase
{
    public function testConstructor(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';

        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $this->assertEquals($conversationId, $request->conversationId);
        $this->assertEquals($message, $request->message);
    }
}
