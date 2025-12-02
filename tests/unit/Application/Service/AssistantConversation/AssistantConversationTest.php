<?php

namespace Chatbot\Tests\Unit\Application\Service\AssistantConversation;

use Chatbot\Application\Service\AssistantConversation\AssistantConversation;
use Chatbot\Application\Service\AssistantConversation\AssistantConversationRequest;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;
use Chatbot\Application\Service\Exception\AssistantMessageNotFoundException;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use PHPUnit\Framework\TestCase;

class AssistantConversationTest extends TestCase
{
    public function testShouldCreateConversationWithAssistant(): void
    {
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
        $threadRepository = $this->createMock(ThreadRepositoryInterface::class);
        $assistantService = $this->createMock(AssistantApi::class);

        $assistantService->expects($this->once())
            ->method('createThread')
            ->willReturn('thr_123');

        $assistantService->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', 'Bonjour, comment allez-vous ?', 'user')
            ->willReturn('msg_123');

        $assistantService->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $assistantService->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $assistantService->expects($this->once())
            ->method('getMessages')
            ->with('thr_123')
            ->willReturn([
                [
                    'role' => 'assistant',
                    'content' => [
                        ['text' => ['value' => 'Je vais très bien, merci !']]
                    ]
                ]
            ]);

        $service =         new AssistantConversation(
            $conversationRepository,
            $contextRepository,
            $threadRepository,
            $assistantService
        );

        $service->execute($request);

        $response = $service->getResponse();
        $this->assertEquals('Je vais très bien, merci !', $response->assistantMessage);
        $this->assertNotEmpty($response->conversationId);
    }


    public function testShouldHandleNoAssistantMessageFound(): void
    {
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
        $threadRepository = $this->createMock(ThreadRepositoryInterface::class);
        $assistantService = $this->createMock(AssistantApi::class);

        $assistantService->expects($this->once())
            ->method('createThread')
            ->willReturn('thr_123');

        $assistantService->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', 'Bonjour, comment allez-vous ?', 'user')
            ->willReturn('msg_123');

        $assistantService->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $assistantService->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $assistantService->expects($this->once())
            ->method('getMessages')
            ->with('thr_123')
            ->willReturn([
                [
                    'role' => 'user',
                    'content' => [
                        ['text' => ['value' => 'Message utilisateur']]
                    ]
                ]
            ]);

        $service =         new AssistantConversation(
            $conversationRepository,
            $contextRepository,
            $threadRepository,
            $assistantService
        );

        $this->expectException(AssistantMessageNotFoundException::class);
        $this->expectExceptionMessage("Aucun message de l'assistant trouvé");

        $service->execute($request);
    }

    public function testShouldCleanMetadataFromMessage(): void
    {
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
        $threadRepository = $this->createMock(ThreadRepositoryInterface::class);
        $assistantService = $this->createMock(AssistantApi::class);

        $assistantService->expects($this->once())
            ->method('createThread')
            ->willReturn('thr_123');

        $assistantService->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', 'Bonjour, comment allez-vous ?', 'user')
            ->willReturn('msg_123');

        $assistantService->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $assistantService->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $assistantService->expects($this->once())
            ->method('getMessages')
            ->with('thr_123')
            ->willReturn([
                [
                    'role' => 'assistant',
                    'content' => [
                        ['text' => ['value' => 'Je vais très bien, merci !【1:2†source】']]
                    ]
                ]
            ]);

        $service =         new AssistantConversation(
            $conversationRepository,
            $contextRepository,
            $threadRepository,
            $assistantService
        );

        $service->execute($request);

        $response = $service->getResponse();
        $this->assertEquals('Je vais très bien, merci !', $response->assistantMessage);
        $this->assertNotEmpty($response->conversationId);
    }

    public function testShouldReuseExistingContextWhenInstructionsAreSame(): void
    {
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
        $threadRepository = $this->createMock(ThreadRepositoryInterface::class);
        $assistantService = $this->createMock(AssistantApi::class);

        $existingContext = $this->createMock(\Chatbot\Domain\Model\Context\Context::class);
        $existingContext->method('getContextId')->willReturn(new \Chatbot\Domain\Model\Context\ContextId());

        $contextRepository->expects($this->once())
            ->method('findByMessage')
            ->with('Tu es un assistant utile')
            ->willReturn($existingContext);

        $contextRepository->expects($this->never())
            ->method('add');

        $assistantService->expects($this->once())
            ->method('createThread')
            ->willReturn('thr_123');

        $assistantService->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', 'Bonjour, comment allez-vous ?', 'user')
            ->willReturn('msg_123');

        $assistantService->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $assistantService->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $assistantService->expects($this->once())
            ->method('getMessages')
            ->with('thr_123')
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
            $threadRepository,
            $assistantService
        );

        $service->execute($request);

        $response = $service->getResponse();
        $this->assertEquals('Je vais très bien, merci !', $response->assistantMessage);
        $this->assertNotEmpty($response->conversationId);
    }
}
