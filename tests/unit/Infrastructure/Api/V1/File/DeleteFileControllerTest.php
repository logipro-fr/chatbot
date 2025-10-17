<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Chatbot\Infrastructure\Api\V1\File\DeleteFileController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteFileControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $controller = new DeleteFileController($fileApi, $fileMetadataRepository);

        $this->assertInstanceOf(DeleteFileController::class, $controller);
    }

    public function testDeleteWithValidFileId(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';

        $fileMetadataRepository->expects($this->once())
            ->method('delete')
            ->with($this->isInstanceOf(FileId::class));

        $fileApi->expects($this->once())
            ->method('delete')
            ->with($fileId);

        $controller = new DeleteFileController($fileApi, $fileMetadataRepository);

        $response = $controller->delete($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteWithEmptyFileId(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $controller = new DeleteFileController($fileApi, $fileMetadataRepository);

        $response = $controller->delete('');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testDeleteWithFileMetadataRepositoryException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';

        $fileMetadataRepository->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('Database error'));

        $controller = new DeleteFileController($fileApi, $fileMetadataRepository);

        $response = $controller->delete($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testDeleteWithFileApiException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';

        $fileMetadataRepository->expects($this->once())
            ->method('delete');

        $fileApi->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception('API error'));

        $controller = new DeleteFileController($fileApi, $fileMetadataRepository);

        $response = $controller->delete($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
