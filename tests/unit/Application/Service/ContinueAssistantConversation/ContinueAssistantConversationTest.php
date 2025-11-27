<?php

namespace Chatbot\Tests\Application\Service\ContinueAssistantConversation;

use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversation;
use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationRequest;
use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\Exception\AssistantMessageNotFoundException;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ContinueAssistantConversationTest extends TestCase
{
    private ConversationRepositoryInterface&MockObject $conversationRepository;
    private ThreadRepositoryInterface&MockObject $threadRepository;
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private AssistantApi&MockObject $assistantApi;
    private ContinueAssistantConversation $continueAssistantConversation;

    protected function setUp(): void
    {
        $this->conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $this->threadRepository = $this->createMock(ThreadRepositoryInterface::class);
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->assistantApi = $this->createMock(AssistantApi::class);

        $this->continueAssistantConversation = new ContinueAssistantConversation(
            $this->conversationRepository,
            $this->threadRepository,
            $this->assistantRepository,
            $this->assistantApi
        );
    }

    public function testExecuteWithValidData(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';
        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getConversationId')->willReturn($conversationId);
        $conversation->expects($this->once())->method('addPair');

        $thread = $this->createMock(Thread::class);
        $thread->method('getExternalThreadId')->willReturn('thr_123');
        $thread->method('getAssistantId')->willReturn(new AssistantId('ast_123'));

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getExternalAssistantId')->willReturn('asst_123');

        $this->conversationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $this->threadRepository
            ->expects($this->once())
            ->method('findByConversationId')
            ->with($conversationId)
            ->willReturn($thread);

        $this->assistantApi
            ->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', $message, 'user');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with(new AssistantId('ast_123'))
            ->willReturn($assistant);

        $this->assistantApi
            ->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $this->assistantApi
            ->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $this->assistantApi
            ->expects($this->once())
            ->method('getMessages')
            ->with('thr_123')
            ->willReturn([
                [
                    'role' => 'assistant',
                    'content' => [
                        ['text' => ['value' => 'Je peux vous aider avec vos questions !']]
                    ]
                ]
            ]);

        $this->continueAssistantConversation->execute($request);

        $response = $this->continueAssistantConversation->getResponse();
        $this->assertInstanceOf(ContinueAssistantConversationResponse::class, $response);
        $this->assertEquals('test-conversation-id', $response->conversationId);
        $this->assertEquals('Je peux vous aider avec vos questions !', $response->assistantMessage);
    }

    public function testExecuteWithNoThreadFound(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';
        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $conversation = $this->createMock(Conversation::class);

        $this->conversationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $this->threadRepository
            ->expects($this->once())
            ->method('findByConversationId')
            ->with($conversationId)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Aucun thread trouvé pour cette conversation');

        $this->continueAssistantConversation->execute($request);
    }

    public function testExecuteWithNoAssistantFound(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';
        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $conversation = $this->createMock(Conversation::class);

        $thread = $this->createMock(Thread::class);
        $thread->method('getExternalThreadId')->willReturn('thr_123');
        $thread->method('getAssistantId')->willReturn(new AssistantId('ast_123'));

        $this->conversationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $this->threadRepository
            ->expects($this->once())
            ->method('findByConversationId')
            ->with($conversationId)
            ->willReturn($thread);

        $this->assistantApi
            ->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', $message, 'user');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with(new AssistantId('ast_123'))
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Assistant non trouvé');

        $this->continueAssistantConversation->execute($request);
    }


    public function testExecuteWithNoAssistantMessageFound(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';
        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $conversation = $this->createMock(Conversation::class);

        $thread = $this->createMock(Thread::class);
        $thread->method('getExternalThreadId')->willReturn('thr_123');
        $thread->method('getAssistantId')->willReturn(new AssistantId('ast_123'));

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getExternalAssistantId')->willReturn('asst_123');

        $this->conversationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $this->threadRepository
            ->expects($this->once())
            ->method('findByConversationId')
            ->with($conversationId)
            ->willReturn($thread);

        $this->assistantApi
            ->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', $message, 'user');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with(new AssistantId('ast_123'))
            ->willReturn($assistant);

        $this->assistantApi
            ->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $this->assistantApi
            ->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'completed']);

        $this->assistantApi
            ->expects($this->once())
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

        $this->expectException(AssistantMessageNotFoundException::class);
        $this->expectExceptionMessage("Aucun message de l'assistant trouvé");

        $this->continueAssistantConversation->execute($request);
    }

    public function testExecuteWithRunFailure(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';
        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $conversation = $this->createMock(Conversation::class);

        $thread = $this->createMock(Thread::class);
        $thread->method('getExternalThreadId')->willReturn('thr_123');
        $thread->method('getAssistantId')->willReturn(new AssistantId('ast_123'));

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getExternalAssistantId')->willReturn('asst_123');

        $this->conversationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $this->threadRepository
            ->expects($this->once())
            ->method('findByConversationId')
            ->with($conversationId)
            ->willReturn($thread);

        $this->assistantApi
            ->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', $message, 'user');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with(new AssistantId('ast_123'))
            ->willReturn($assistant);

        $this->assistantApi
            ->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $this->assistantApi
            ->expects($this->once())
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturn(['status' => 'failed']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Le run a échoué avec le statut: failed");

        $this->continueAssistantConversation->execute($request);
    }


    public function testExecuteWithProgressiveDelay(): void
    {
        $conversationId = new ConversationId('test-conversation-id');
        $message = 'Comment puis-je vous aider ?';
        $request = new ContinueAssistantConversationRequest($conversationId, $message);

        $conversation = $this->createMock(Conversation::class);
        $conversation->method('getConversationId')->willReturn($conversationId);

        $thread = $this->createMock(Thread::class);
        $thread->method('getExternalThreadId')->willReturn('thr_123');
        $thread->method('getAssistantId')->willReturn(new AssistantId('ast_123'));

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getExternalAssistantId')->willReturn('asst_123');

        $this->conversationRepository
            ->expects($this->once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $this->threadRepository
            ->expects($this->once())
            ->method('findByConversationId')
            ->with($conversationId)
            ->willReturn($thread);

        $this->assistantApi
            ->expects($this->once())
            ->method('addMessageToThread')
            ->with('thr_123', $message, 'user');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with(new AssistantId('ast_123'))
            ->willReturn($assistant);

        $this->assistantApi
            ->expects($this->once())
            ->method('createRun')
            ->with('thr_123', 'asst_123')
            ->willReturn('run_123');

        $this->assistantApi
            ->expects($this->exactly(2))
            ->method('getRunStatus')
            ->with('thr_123', 'run_123')
            ->willReturnOnConsecutiveCalls(
                ['status' => 'in_progress'],
                ['status' => 'completed']
            );

        $this->assistantApi
            ->expects($this->once())
            ->method('getMessages')
            ->with('thr_123')
            ->willReturn([
                [
                    'role' => 'assistant',
                    'content' => [
                        ['text' => ['value' => 'Je peux vous aider avec vos questions !']]
                    ]
                ]
            ]);

        $this->continueAssistantConversation->execute($request);

        $response = $this->continueAssistantConversation->getResponse();
        $this->assertInstanceOf(ContinueAssistantConversationResponse::class, $response);
        $this->assertEquals('test-conversation-id', $response->conversationId);
        $this->assertEquals('Je peux vous aider avec vos questions !', $response->assistantMessage);
    }
}
