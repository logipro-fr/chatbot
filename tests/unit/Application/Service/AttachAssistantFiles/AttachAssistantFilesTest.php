<?php

namespace Chatbot\Tests\Application\Service\AttachAssistantFiles;

use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFiles;
use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFilesRequest;
use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AttachAssistantFilesTest extends TestCase
{
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private AssistantApi&MockObject $assistantApi;
    private AttachAssistantFiles $AttachAssistantFiles;

    protected function setUp(): void
    {
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->assistantApi = $this->createMock(AssistantApi::class);
        $this->AttachAssistantFiles = new AttachAssistantFiles($this->assistantRepository, $this->assistantApi);
    }

    public function testExecuteWithNonExistentAssistant(): void
    {
        $assistantId = new AssistantId('non-existent-id');
        $fileIds = ['file1', 'file2'];
        $request = new AttachAssistantFilesRequest($assistantId, $fileIds);

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with($assistantId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assistant not found: ' . $assistantId->getId());

        $this->AttachAssistantFiles->execute($request);
    }


    public function testAssistantAttachFileInVectorStore(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = ['file1'];
        $request = new AttachAssistantFilesRequest($assistantId, $fileIds);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);
        $assistant->method('getVectorId')->willReturn('vector123');

        $assistant->method('getFileIds')
           ->willReturnOnConsecutiveCalls(
               ['old-file1'],
               $fileIds
           );

        $assistant->expects($this->once())
           ->method('addFileId')
           ->with('file1');

        $assistant->method('getExternalAssistantId')
           ->willReturn('asst_external123');

        $this->assistantRepository
           ->expects($this->once())
           ->method('findById')
           ->with($assistantId)
           ->willReturn($assistant);

        $this->assistantApi
        ->expects($this->once())
        ->method('updateAssistantFile')
        ->with(
            'asst_external123',
            $assistant,
            $fileIds,
            'vector123'
        );

        $this->assistantRepository
           ->expects($this->once())
           ->method('add')
           ->with($assistant);

        $this->AttachAssistantFiles->execute($request);

        $response = $this->AttachAssistantFiles->getResponse();
        $this->assertInstanceOf(AttachAssistantFilesResponse::class, $response);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($fileIds, $response->fileIds);
    }
}
