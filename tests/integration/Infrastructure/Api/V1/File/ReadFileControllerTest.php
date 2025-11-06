<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\File\ReadFileController;
use Chatbot\Tests\Infrastructure\Api\V1\AssertResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ReadFileControllerTest extends TestCase
{
    use AssertResponseTrait;

    private ReadFileControllerFileApiTest|ReadFileControllerFileApiErrorTest $fileApi;
    private ReadFileController $controller;

    protected function setUp(): void
    {
        $this->fileApi = new ReadFileControllerFileApiTest();
        $this->controller = new ReadFileController($this->fileApi, new FileMetadataRepositoryTest());
    }

    public function testListFilesSuccessfully(): void
    {
        $response = $this->controller->list();

        $this->assertOnlySuccess($response);

        $content = $response->getContent();
        if ($content === false) {
            $this->fail('Failed to get response content');
        }
        $data = json_decode($content, true);
        /** @var array<string, mixed> $data */
        $dataArray = $data['data'] ?? [];
        if (is_array($dataArray)) {
            $this->assertIsArray($dataArray['files'] ?? []);
            $this->assertEquals(2, $dataArray['count'] ?? 0);
        }
    }

    public function testGetFileSuccessfully(): void
    {
        $fileId = 'fil-abc123';

        $response = $this->controller->get($fileId);

        $this->assertOnlySuccess($response);

        $content = $response->getContent();
        if ($content === false) {
            $this->fail('Failed to get response content');
        }
        $data = json_decode($content, true);
        /** @var array<string, mixed> $data */
        $dataArray = $data['data'] ?? [];
        if (is_array($dataArray)) {
            $fileData = $dataArray['file'] ?? [];
            $this->assertIsArray($fileData);
            $this->assertEquals($fileId, $fileData['id'] ?? '');
        }
    }

    public function testGetFileWithEmptyFileId(): void
    {
        $fileId = '';

        $response = $this->controller->get($fileId);

        $this->assertResponseFailure($response, 'InvalidArgumentException');
    }

    public function testListFilesWithApiError(): void
    {
        $this->fileApi = new ReadFileControllerFileApiErrorTest();
        $this->controller = new ReadFileController($this->fileApi, new FileMetadataRepositoryTest());

        $response = $this->controller->list();

        $this->assertResponseFailure($response, 'Exception');
    }

    public function testGetFileWithApiError(): void
    {
        $this->fileApi = new ReadFileControllerFileApiErrorTest();
        $this->controller = new ReadFileController($this->fileApi, new FileMetadataRepositoryTest());

        $fileId = 'fil-abc123';

        $response = $this->controller->get($fileId);

        $this->assertResponseFailure($response, 'Exception');
    }
}
