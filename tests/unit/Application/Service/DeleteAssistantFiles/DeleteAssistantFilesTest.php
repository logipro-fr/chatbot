<?php

namespace tests\unit\Application\Service\DeleteAssistantFiles;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFiles;
use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFilesRequest;
use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\File\FileId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteAssistantFilesTest extends TestCase
{
    private AssistantRepositoryInterface&MockObject $assistantRepository;
    private AssistantApi&MockObject $assistantApi;
    private DeleteAssistantFiles $deleteAssistantFiles;

    public function setUp(): void
    {
        $this->assistantRepository = $this->createMock(AssistantRepositoryInterface::class);
        $this->assistantApi = $this->createMock(AssistantApi::class);
        $this->deleteAssistantFiles = new DeleteAssistantFiles($this->assistantRepository, $this->assistantApi);
    }

    public function testExecute(): void
    {
        $assistantId = new AssistantId('ast_123');
        $initialFileIds = ['fil_1', 'fil_2'];       // avant suppression
        $file_id = new FileId('fil_1');             // fichier à supprimer
        $expectedRemainingFileIds = ['fil_2'];      // après suppression
        $vectorId = 'vs_123456';
        $request = new DeleteAssistantFilesRequest($assistantId, $file_id);

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

        $this->deleteAssistantFiles->execute($request);

        $response = $this->deleteAssistantFiles->getResponse();

        $this->assertInstanceOf(DeleteAssistantFilesResponse::class, $response);
        $this->assertEquals($expectedRemainingFileIds, $response->fileIds);
        $this->assertContains('fil_2', $response->fileIds);
        $this->assertNotContains('fil_1', $response->fileIds);
    }


    public function testExecuteWithNonExistentAssistant(): void
    {
        $assistantId = new AssistantId('ast_123');
        $file_id = new FileId('fil_1');             // fichier à supprimer
        $request = new DeleteAssistantFilesRequest($assistantId, $file_id);

        $this->assistantRepository
        ->expects($this->once())
        ->method('findById')
        ->with($assistantId)
        ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Assistant not found: ' . $assistantId->getId());

        $this->deleteAssistantFiles->execute($request);
    }
}
