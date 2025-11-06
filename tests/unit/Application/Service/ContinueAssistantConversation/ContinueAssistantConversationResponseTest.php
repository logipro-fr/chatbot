<?php

namespace Chatbot\Tests\Application\Service\ContinueAssistantConversation;

use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationResponse;
use PHPUnit\Framework\TestCase;

class ContinueAssistantConversationResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $conversationId = 'test-conversation-id';
        $assistantMessage = 'Je peux vous aider avec vos questions !';

        $response = new ContinueAssistantConversationResponse($conversationId, $assistantMessage);

        $this->assertEquals($conversationId, $response->conversationId);
        $this->assertEquals($assistantMessage, $response->assistantMessage);
    }
}
