<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Chatbot\Infrastructure\Api\V1\File\ReadFileController;
use Chatbot\Infrastructure\Exception\FileNotFoundException;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ReadFileControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $this->assertInstanceOf(ReadFileController::class, $controller);
    }

    public function testListWithFiles(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $openAiFiles = [
            ['id' => 'fil-abc123', 'filename' => 'test1.txt'],
            ['id' => 'fil-def456', 'filename' => 'test2.txt']
        ];

        $fileMetadata = $this->createMock(FileMetadata::class);
        $fileMetadata->expects($this->once())
            ->method('getOriginalFilename')
            ->willReturn('original-test1.txt');

        $fileApi->expects($this->once())
            ->method('list')
            ->willReturn($openAiFiles);

        $fileMetadataRepository->expects($this->exactly(2))
            ->method('findById')
            ->willReturnCallback(function (FileId $id) use ($fileMetadata) {
                if ($id->getId() === 'fil-abc123') {
                    return $fileMetadata;
                }
                return null;
            });

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->list();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListWithEmptyFiles(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileApi->expects($this->once())
            ->method('list')
            ->willReturn([]);

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->list();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListWithException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileApi->expects($this->once())
            ->method('list')
            ->willThrowException(new \Exception('API error'));

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->list();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testGetWithValidFileId(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';
        $fileInfo = ['id' => $fileId, 'filename' => 'test.txt'];

        $fileMetadata = $this->createMock(FileMetadata::class);
        $fileMetadata->method('getOriginalFilename')->willReturn('original-test.txt');

        $fileApi->expects($this->once())
            ->method('get')
            ->with($fileId)
            ->willReturn($fileInfo);

        $fileMetadataRepository->expects($this->once())
            ->method('findById')
            ->with(new FileId($fileId))
            ->willReturn($fileMetadata);

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->get($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetWithEmptyFileId(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->get('');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testGetWithoutMetadata(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';
        $fileInfo = ['id' => $fileId, 'filename' => 'test.txt'];

        $fileApi->expects($this->once())
            ->method('get')
            ->with($fileId)
            ->willReturn($fileInfo);

        $fileMetadataRepository->expects($this->once())
            ->method('findById')
            ->with(new FileId($fileId))
            ->willReturn(null);

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->get($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetWithFileNotFoundException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';

        $fileApi->expects($this->once())
            ->method('get')
            ->with($fileId)
            ->willThrowException(new FileNotFoundException(
                'File not found: The requested file does not exist or has been deleted.'
            ));

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->get($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetWithException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $fileId = 'fil-abc123';

        $fileApi->expects($this->once())
            ->method('get')
            ->with($fileId)
            ->willThrowException(new \Exception('API error'));

        $controller = new ReadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->get($fileId);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
