<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\File\ReadFileController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Chatbot\Tests\Infrastructure\Api\V1\AssertResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ReadFileControllerTest extends TestCase
{
    use AssertResponseTrait;

    private FileApi $fileApi;
    private ReadFileController $controller;

    protected function setUp(): void
    {
        $this->fileApi = new ReadFileControllerFileApiTest();
        $this->controller = new ReadFileController($this->fileApi, new FileMetadataRepositoryTest());
    }

    public function testListFilesSuccessfully(): void
    {
        $request = new Request();

        $response = $this->controller->list($request);

        $this->assertOnlySuccess($response);

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data['data']->files);
        $this->assertEquals(2, $data['data']->count);
    }

    public function testGetFileSuccessfully(): void
    {
        $request = new Request();
        $fileId = 'file-abc123';

        $response = $this->controller->get($request, $fileId);

        $this->assertOnlySuccess($response);

        $data = json_decode($response->getContent(), true);
        $this->assertIsObject($data['data']->file);
        $this->assertEquals($fileId, $data['data']->file->id);
    }

    public function testGetFileWithEmptyFileId(): void
    {
        $request = new Request();
        $fileId = '';

        $response = $this->controller->get($request, $fileId);

        $this->assertResponseFailure($response, 'InvalidArgumentException');
    }

    public function testListFilesWithApiError(): void
    {
        $this->fileApi = new ReadFileControllerFileApiErrorTest();
        $this->controller = new ReadFileController($this->fileApi, new FileMetadataRepositoryTest());

        $request = new Request();

        $response = $this->controller->list($request);

        $this->assertResponseFailure($response, 'Exception');
    }

    public function testGetFileWithApiError(): void
    {
        $this->fileApi = new ReadFileControllerFileApiErrorTest();
        $this->controller = new ReadFileController($this->fileApi, new FileMetadataRepositoryTest());

        $request = new Request();
        $fileId = 'file-abc123';

        $response = $this->controller->get($request, $fileId);

        $this->assertResponseFailure($response, 'Exception');
    }
}

class ReadFileControllerFileApiTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new \Symfony\Component\HttpClient\MockHttpClient());
    }

    public function list(): array
    {
        return [
            [
                'id' => 'file-abc123',
                'filename' => 'document1.pdf',
                'purpose' => 'assistants',
                'bytes' => 1024
            ],
            [
                'id' => 'file-def456',
                'filename' => 'document2.pdf',
                'purpose' => 'assistants',
                'bytes' => 2048
            ]
        ];
    }

    public function get(string $fileId): array
    {
        return [
            'id' => $fileId,
            'filename' => 'document.pdf',
            'purpose' => 'assistants',
            'bytes' => 1024
        ];
    }
}

class ReadFileControllerFileApiErrorTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new \Symfony\Component\HttpClient\MockHttpClient());
    }

    public function list(): array
    {
        throw new \Exception('API Error');
    }

    public function get(string $fileId): array
    {
        throw new \Exception('API Error');
    }
}
