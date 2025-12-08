<?php

namespace Chatbot\Tests\Application\Service\UpdateAssistantFiles;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFiles;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UpdateAssistantFilesTest extends TestCase
{
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private AssistantApi&MockObject $assistantApi;
    private UpdateAssistantFiles $updateAssistantFiles;

    protected function setUp(): void
    {
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->assistantApi = $this->createMock(AssistantApi::class);
        $this->updateAssistantFiles = new UpdateAssistantFiles($this->assistantRepository, $this->assistantApi);
    }

    public function testExecuteWithValidAssistant(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = ['file1', 'file2', 'file3'];
        $request = new UpdateAssistantFilesRequest($assistantId, $fileIds, 'vs_123456');

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);

        $assistant->method('getFileIds')
            ->willReturnOnConsecutiveCalls(
                ['old-file1', 'old-file2'],
                $fileIds
            );

        $assistant->expects($this->exactly(2))
            ->method('removeFileId');

        $assistant->expects($this->exactly(3))
            ->method('addFileId');

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
            ->with('asst_external123', $fileIds);

        $this->assistantRepository
            ->expects($this->once())
            ->method('add')
            ->with($assistant);

        $this->updateAssistantFiles->execute($request);

        $response = $this->updateAssistantFiles->getResponse();
        $this->assertInstanceOf(UpdateAssistantFilesResponse::class, $response);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($fileIds, $response->fileIds);
    }

    public function testExecuteWithNonExistentAssistant(): void
    {
        $assistantId = new AssistantId('non-existent-id');
        $fileIds = ['file1', 'file2'];
        $request = new UpdateAssistantFilesRequest($assistantId, $fileIds, 'vs_123456');

        $this->assistantRepository
            ->expects($this->once())
            ->method('findById')
            ->with($assistantId)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assistant not found: ' . $assistantId->getId());

        $this->updateAssistantFiles->execute($request);
    }

    public function testExecuteWithEmptyFileIds(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = [];
        $vectorId = 'vs_123456';
        $request = new UpdateAssistantFilesRequest($assistantId, $fileIds, $vectorId);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);

        $assistant->method('getFileIds')
            ->willReturnOnConsecutiveCalls(
                ['old-file1'],
                $fileIds
            );

        $assistant->expects($this->once())
            ->method('removeFileId')
            ->with('old-file1');

        $assistant->expects($this->never())
            ->method('addFileId');

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
            ->with('asst_external123', $fileIds);

        $this->assistantRepository
            ->expects($this->once())
            ->method('add')
            ->with($assistant);

        $this->updateAssistantFiles->execute($request);

        $response = $this->updateAssistantFiles->getResponse();
        $this->assertInstanceOf(UpdateAssistantFilesResponse::class, $response);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($fileIds, $response->fileIds);
    }

     public function testAssistantAddFileInVectorStore(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = ['file1'];
        $vectorId = null;
        $request = new UpdateAssistantFilesRequest($assistantId, $fileIds, $vectorId);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn($assistantId);

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
            ->with('asst_external123', $fileIds, $vectorId);
        
        $this->assistantRepository
            ->expects($this->once())
            ->method('add')
            ->with($assistant);

        $this->updateAssistantFiles->execute($request);

        $response = $this->updateAssistantFiles->getResponse();
        $this->assertInstanceOf(UpdateAssistantFilesResponse::class, $response);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($fileIds, $response->fileIds);
        $this->assertEquals($vectorId, $response->vectorId);
    }
}
