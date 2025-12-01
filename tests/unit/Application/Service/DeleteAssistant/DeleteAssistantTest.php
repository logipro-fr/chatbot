<?php

namespace Chatbot\Tests\Application\Service\DeleteAssistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistant;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantRequest;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DeleteAssistantTest extends TestCase
{
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private AssistantApi&MockObject $assistantApi;
    private DeleteAssistant $deleteAssistant;

    protected function setUp(): void
    {
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->assistantApi = $this->createMock(AssistantApi::class);
        $this->deleteAssistant = new DeleteAssistant($this->assistantRepository, $this->assistantApi);
    }

    public function testExecuteWithValidAssistant(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $request = new DeleteAssistantRequest($assistantId);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getExternalAssistantId')->willReturn('external-assistant-id');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with($assistantId)
            ->willReturn($assistant);

        $this->assistantApi
            ->expects($this->once())
            ->method('deleteAssistant')
            ->with('external-assistant-id');

        $this->assistantRepository
            ->expects($this->once())
            ->method('delete')
            ->with($assistantId);

        $this->deleteAssistant->execute($request);

        $response = $this->deleteAssistant->getResponse();
        $this->assertInstanceOf(DeleteAssistantResponse::class, $response);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals('external-assistant-id', $response->externalAssistantId);
    }

    public function testExecuteWithNonExistentAssistant(): void
    {
        $assistantId = new AssistantId('non-existent-id');
        $request = new DeleteAssistantRequest($assistantId);

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with($assistantId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assistant non trouvé: ' . $assistantId->getId());

        $this->deleteAssistant->execute($request);
    }

    public function testExecuteWithAssistantApiException(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $request = new DeleteAssistantRequest($assistantId);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getExternalAssistantId')->willReturn('external-assistant-id');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with($assistantId)
            ->willReturn($assistant);

        $this->assistantApi
            ->expects($this->once())
            ->method('deleteAssistant')
            ->with('external-assistant-id')
            ->willThrowException(new \Exception('API Error'));

        $this->assistantRepository
            ->expects($this->once())
            ->method('delete')
            ->with($assistantId);

        $this->deleteAssistant->execute($request);

        $response = $this->deleteAssistant->getResponse();
        $this->assertInstanceOf(DeleteAssistantResponse::class, $response);
    }
}
