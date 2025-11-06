<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\File\UploadFileController;
use Chatbot\Tests\Infrastructure\Api\V1\AssertResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UploadFileControllerTest extends TestCase
{
    use AssertResponseTrait;

    private UploadFileControllerFileApiTest|UploadFileControllerFileApiErrorTest $fileApi;
    private UploadFileController $controller;

    protected function setUp(): void
    {
        $this->fileApi = new UploadFileControllerFileApiTest();
        $this->controller = new UploadFileController($this->fileApi, new FileMetadataRepositoryTest());
    }

    public function testUploadFileSuccessfully(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
        file_put_contents($tempFile, 'test content');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.pdf',
            'application/pdf',
            null,
            true
        );

        $request = new Request();
        $request->files->set('file', $uploadedFile);
        $request->request->set('purpose', 'assistants');

        $response = $this->controller->upload($request);

        $this->assertOnlySuccess($response);

        $content = $response->getContent();
        if ($content === false) {
            $this->fail('Failed to get response content');
        }
        $data = json_decode($content, true);
        /** @var array<string, mixed> $data */
        $dataArray = $data['data'] ?? [];
        if (is_array($dataArray)) {
            $this->assertEquals('fil-abc123', $dataArray['file_id'] ?? '');
            $this->assertEquals('test.pdf', $dataArray['filename'] ?? '');
            $this->assertEquals('assistants', $dataArray['purpose'] ?? '');
        }

        unlink($tempFile);
    }

    public function testUploadFileWithoutFile(): void
    {
        $request = new Request();

        $response = $this->controller->upload($request);

        $this->assertResponseFailure($response, 'InvalidArgumentException');
    }

    public function testUploadFileWithInvalidPurpose(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
        file_put_contents($tempFile, 'test content');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.pdf',
            'application/pdf',
            null,
            true
        );

        $request = new Request();
        $request->files->set('file', $uploadedFile);
        $request->request->set('purpose', 'fine-tune');

        $response = $this->controller->upload($request);

        $this->assertResponseFailure($response, 'InvalidArgumentException');

        unlink($tempFile);
    }

    public function testUploadFileWithApiError(): void
    {
        $this->fileApi = new UploadFileControllerFileApiErrorTest();
        $this->controller = new UploadFileController($this->fileApi, new FileMetadataRepositoryTest());

        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.pdf';
        file_put_contents($tempFile, 'test content');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'test.pdf',
            'application/pdf',
            null,
            true
        );

        $request = new Request();
        $request->files->set('file', $uploadedFile);
        $request->request->set('purpose', 'assistants');

        $response = $this->controller->upload($request);

        $this->assertResponseFailure($response, 'Exception');

        unlink($tempFile);
    }
}
