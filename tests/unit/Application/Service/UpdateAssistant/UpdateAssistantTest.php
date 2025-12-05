<?php

namespace Chatbot\Application\Service\UpdateAssistant;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateAssistantTest extends TestCase
{
    private AssistantApi&MockObject $assistantApi;
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private UpdateAssistant $updateAssistant;

    public function setUp(): void
    {
        $this->assistantApi = $this->createMock(AssistantApi::class);
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->updateAssistant = new UpdateAssistant($this->assistantRepository, $this->assistantApi);
    }

    public function testExecute(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $newName = 'Updated Assistant Name';
        $newInstructions = 'Updated instructions for the assistant.';
        $request = new UpdateAssistantRequest($assistantId, $newName, $newInstructions);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getExternalAssistantId')->willReturn('asst_external123');

        $assistant->method('getName')->willReturn($newName);
        $assistant->method('getInstructions')->willReturn($newInstructions);

        $this->assistantApi
        ->expects($this->once())
        ->method('updateAssistant')
        ->with('asst_external123', $newName, $newInstructions);

        $assistant->expects($this->once())->method('setName')->with($newName);
        $assistant->expects($this->once())->method('setInstructions')->with($newInstructions);

        $this->assistantRepository
        ->expects($this->once())
        ->method('findById')
        ->with($assistantId)
        ->willReturn($assistant);

        $this->updateAssistant->execute($request);

        $response = $this->updateAssistant->getResponse();
        $this->assertInstanceOf(UpdateAssistantResponse::class, $response);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($newName, $response->newName);
        $this->assertEquals($newInstructions, $response->newInstructions);
    }
}
