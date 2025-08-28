<?php

namespace Chatbot\Tests\Unit\Application\Service\AssistantConversation;

use Chatbot\Application\Service\AssistantConversation\AssistantConversation;
use Chatbot\Application\Service\AssistantConversation\AssistantConversationRequest;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use PHPUnit\Framework\TestCase;

class AssistantConversationTest extends TestCase
{
    public function test_should_create_conversation_with_assistant(): void
    {
        // Given
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Tu es un assistant utile",
            "asst_123"
        );

        $request = new AssistantConversationRequest(
            $assistant,
            "Bonjour, comment allez-vous ?"
        );

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $contextRepository = $this->createMock(ContextRepositoryInterface::class);
        $assistantApi = $this->createMock(AssistantApi::class);

        $assistantApi->expects($this->once())
            ->method('createThread')
            ->willReturn('thread_123');

        $assistantApi->expects($this->once())
            ->method('addMessageToThread')
            ->with('thread_123', 'Bonjour, comment allez-vous ?', 'user')
            ->willReturn('msg_123');

        $assistantApi->expects($this->once())
            ->method('createRun')
            ->with('thread_123', 'asst_123')
            ->willReturn('run_123');

        $assistantApi->expects($this->once())
            ->method('getRunStatus')
            ->with('thread_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $assistantApi->expects($this->once())
            ->method('getMessages')
            ->with('thread_123')
            ->willReturn([
                [
                    'role' => 'assistant',
                    'content' => [
                        ['text' => ['value' => 'Je vais très bien, merci !']]
                    ]
                ]
            ]);

        $service = new AssistantConversation(
            $conversationRepository,
            $contextRepository,
            $assistantApi
        );

        // When
        $service->execute($request);

        // Then
        $response = $service->getResponse();
        $this->assertNotNull($response);
        $this->assertEquals('Je vais très bien, merci !', $response->assistantMessage);
    }
}
