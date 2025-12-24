<?php

namespace tests\unit\Application\Service\DetachAssistantFiles;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFiles;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFilesRequest;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\File\FileId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DetachAssistantFilesTest extends TestCase
{
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private AssistantApi&MockObject $assistantApi;
    private DetachAssistantFiles $detachAssistantFiles;

    public function setUp(): void
    {
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->assistantApi = $this->createMock(AssistantApi::class);
        $this->detachAssistantFiles = new DetachAssistantFiles($this->assistantRepository, $this->assistantApi);
    }

    public function testExecute(): void
    {
        $assistantId = new AssistantId('ast_123');
        $initialFileIds = ['fil_1', 'fil_2'];       // avant suppression
        $file_id = new FileId('fil_1');             // fichier à supprimer
        $expectedRemainingFileIds = ['fil_2'];      // après suppression
        $vectorId = 'vs_123456';
        $request = new DetachAssistantFilesRequest($assistantId, $file_id);

        $assistant = $this->createMock(Assistant::class);

    // Le repo retourne bien notre assistant
        $this->assistantRepository
        ->expects($this->once())
        ->method('findById')
        ->willReturn($assistant);

    // getVectorId
        $assistant
        ->method('getVectorId')
        ->willReturn($vectorId);

        $assistant
        ->method('getFileIds')
        ->willReturnOnConsecutiveCalls(
            $initialFileIds,
            $expectedRemainingFileIds
        );

        $assistant
        ->expects($this->once())
        ->method('removeFileId')
        ->with($file_id);


        $this->assistantApi
        ->expects($this->once())
        ->method('deleteVectorStoreFile')
        ->with(
            (string) $vectorId,
            (string) $file_id
        );


        $this->assistantRepository
        ->expects($this->once())
        ->method('add')
        ->with($assistant);

        $this->detachAssistantFiles->execute($request);

        $response = $this->detachAssistantFiles->getResponse();

        $this->assertInstanceOf(DetachAssistantFilesResponse::class, $response);
        $this->assertEquals($expectedRemainingFileIds, $response->fileIds);
        $this->assertContains('fil_2', $response->fileIds);
        $this->assertNotContains('fil_1', $response->fileIds);
    }


    public function testExecuteWithNonExistentAssistant(): void
    {
        $assistantId = new AssistantId('ast_123');
        $file_id = new FileId('fil_1');             // fichier à supprimer
        $request = new DetachAssistantFilesRequest($assistantId, $file_id);

        $this->assistantRepository
        ->expects($this->once())
        ->method('findById')
        ->with($assistantId)
        ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assistant not found: ' . $assistantId->getId());

        $this->detachAssistantFiles->execute($request);
    }
}
