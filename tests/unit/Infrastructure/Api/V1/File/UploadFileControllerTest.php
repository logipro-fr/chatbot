<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\File;

use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Chatbot\Infrastructure\Api\V1\File\UploadFileController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadFileControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $controller = new UploadFileController($fileApi, $fileMetadataRepository);

        $this->assertInstanceOf(UploadFileController::class, $controller);
    }

    public function testUploadWithValidFile(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getPathname')->willReturn('/tmp/test.txt');
        $uploadedFile->method('getClientOriginalName')->willReturn('test.txt');
        $uploadedFile->method('getSize')->willReturn(1024);

        $filesBag = new FileBag(['file' => $uploadedFile]);
        $inputBag = new InputBag(['purpose' => 'assistants']);

        $request = $this->createPartialMock(Request::class, []);
        $request->files = $filesBag;
        $request->request = $inputBag;

        $fileApi->expects($this->once())
            ->method('upload')
            ->with('/tmp/test.txt', 'assistants')
            ->willReturn('fil-abc123');

        $fileMetadataRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(FileMetadata::class));

        $controller = new UploadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->upload($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUploadWithNoFile(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $filesBag = new FileBag([]);

        $request = $this->createPartialMock(Request::class, []);
        $request->files = $filesBag;

        $controller = new UploadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->upload($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUploadWithInvalidPurpose(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);

        $filesBag = new FileBag(['file' => $uploadedFile]);
        $inputBag = new InputBag(['purpose' => 'invalid']);

        $request = $this->createPartialMock(Request::class, []);
        $request->files = $filesBag;
        $request->request = $inputBag;

        $controller = new UploadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->upload($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUploadWithFileApiException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getPathname')->willReturn('/tmp/test.txt');

        $filesBag = new FileBag(['file' => $uploadedFile]);
        $inputBag = new InputBag(['purpose' => 'assistants']);

        $request = $this->createPartialMock(Request::class, []);
        $request->files = $filesBag;
        $request->request = $inputBag;

        $fileApi->expects($this->once())
            ->method('upload')
            ->willThrowException(new \Exception('API error'));

        $controller = new UploadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->upload($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testUploadWithFileMetadataRepositoryException(): void
    {
        $fileApi = $this->createMock(FileApi::class);
        $fileMetadataRepository = $this->createMock(FileMetadataRepositoryInterface::class);

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getPathname')->willReturn('/tmp/test.txt');
        $uploadedFile->method('getClientOriginalName')->willReturn('test.txt');
        $uploadedFile->method('getSize')->willReturn(1024);

        $filesBag = new FileBag(['file' => $uploadedFile]);
        $inputBag = new InputBag(['purpose' => 'assistants']);

        $request = $this->createPartialMock(Request::class, []);
        $request->files = $filesBag;
        $request->request = $inputBag;

        $fileApi->expects($this->once())
            ->method('upload')
            ->willReturn('fil-abc123');

        $fileMetadataRepository->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('Database error'));

        $controller = new UploadFileController($fileApi, $fileMetadataRepository);

        $response = $controller->upload($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
