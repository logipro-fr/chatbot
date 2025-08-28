<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\File\DeleteFileController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Chatbot\Tests\Infrastructure\Api\V1\AssertResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class DeleteFileControllerTest extends TestCase
{
    use AssertResponseTrait;

    private FileApi $fileApi;
    private DeleteFileController $controller;

    protected function setUp(): void
    {
        $this->fileApi = new DeleteFileControllerFileApiTest();
        $this->controller = new DeleteFileController($this->fileApi, new FileMetadataRepositoryTest());
    }

    public function testDeleteFileSuccessfully(): void
    {
        $request = new Request();
        $fileId = 'file-abc123';

        $response = $this->controller->delete($request, $fileId);

        $this->assertOnlySuccess($response);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('File deleted successfully', $data['data']->message);
        $this->assertEquals($fileId, $data['data']->file_id);
    }

    public function testDeleteFileWithEmptyFileId(): void
    {
        $request = new Request();
        $fileId = '';

        $response = $this->controller->delete($request, $fileId);

        $this->assertResponseFailure($response, 'InvalidArgumentException');
    }

    public function testDeleteFileWithApiError(): void
    {
        $this->fileApi = new DeleteFileControllerFileApiErrorTest();
        $this->controller = new DeleteFileController($this->fileApi, new FileMetadataRepositoryTest());

        $request = new Request();
        $fileId = 'file-abc123';

        $response = $this->controller->delete($request, $fileId);

        $this->assertResponseFailure($response, 'Exception');
    }
}

class DeleteFileControllerFileApiTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new \Symfony\Component\HttpClient\MockHttpClient());
    }

    public function delete(string $fileId): void
    {
        // Simulation de suppression réussie
    }
}

class FileMetadataRepositoryTest implements \Chatbot\Domain\Model\File\FileMetadataRepositoryInterface
{
    public function save(\Chatbot\Domain\Model\File\FileMetadata $fileMetadata): void
    {
        // Simulation de sauvegarde
    }

    public function findById(\Chatbot\Domain\Model\File\FileId $fileId): ?\Chatbot\Domain\Model\File\FileMetadata
    {
        return null;
    }

    public function findAll(): array
    {
        return [];
    }

    public function delete(\Chatbot\Domain\Model\File\FileId $fileId): void
    {
        // Simulation de suppression réussie
    }

    public function findByPurpose(string $purpose): array
    {
        return [];
    }
}

class DeleteFileControllerFileApiErrorTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new \Symfony\Component\HttpClient\MockHttpClient());
    }

    public function delete(string $fileId): void
    {
        throw new \Exception('API Error');
    }
}
