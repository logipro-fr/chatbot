<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\File\DeleteFileController;
use Chatbot\Tests\Infrastructure\Api\V1\AssertResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class DeleteFileControllerTest extends TestCase
{
    use AssertResponseTrait;

    private DeleteFileControllerFileApiTest|DeleteFileControllerFileApiErrorTest $fileApi;
    private DeleteFileController $controller;

    protected function setUp(): void
    {
        $this->fileApi = new DeleteFileControllerFileApiTest();
        $this->controller = new DeleteFileController($this->fileApi, new FileMetadataRepositoryTest());
    }

    public function testDeleteFileSuccessfully(): void
    {
        $fileId = 'fil-abc123';

        $response = $this->controller->delete($fileId);

        $this->assertOnlySuccess($response);

        $content = $response->getContent();
        if ($content === false) {
            $this->fail('Failed to get response content');
        }
        $data = json_decode($content, true);
        /** @var array<string, mixed> $data */
        $dataArray = $data['data'] ?? [];
        if (is_array($dataArray)) {
            $this->assertEquals('File deleted successfully', $dataArray['message'] ?? '');
            $this->assertEquals($fileId, $dataArray['file_id'] ?? '');
        }
    }

    public function testDeleteFileWithEmptyFileId(): void
    {
        $fileId = '';

        $response = $this->controller->delete($fileId);

        $this->assertResponseFailure($response, 'InvalidArgumentException');
    }

    public function testDeleteFileWithApiError(): void
    {
        $this->fileApi = new DeleteFileControllerFileApiErrorTest();
        $this->controller = new DeleteFileController($this->fileApi, new FileMetadataRepositoryTest());

        $fileId = 'fil-abc123';

        $response = $this->controller->delete($fileId);

        $this->assertResponseFailure($response, 'Exception');
    }
}
