<?php

namespace Chatbot\Tests\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\MissingChatbotKeyApiException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\TooManyRequestException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FileApiTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private FileApi $fileApi;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->fileApi = new FileApi($this->httpClient, 'test-api-key');
    }

    public function testConstructorWithApiKey(): void
    {
        $fileApi = new FileApi($this->httpClient, 'custom-api-key');
        $this->assertInstanceOf(FileApi::class, $fileApi);
    }

    public function testConstructorWithMissingApiKey(): void
    {
        $originalEnv = $_ENV['CHATBOT_KEY_API'] ?? null;
        unset($_ENV['CHATBOT_KEY_API']);

        $this->expectException(MissingChatbotKeyApiException::class);
        $this->expectExceptionMessage(
            'Missing environment variable: CHATBOT_KEY_API is required to initialize FileApi.'
        );

        new FileApi($this->httpClient);

        if ($originalEnv !== null) {
            $_ENV['CHATBOT_KEY_API'] = $originalEnv;
        }
    }

    public function testUploadWithValidFile(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test-file-') . '.txt';
        $fileContent = 'test content';
        file_put_contents($filePath, $fileContent);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn('{"id": "file-123"}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/files',
                $this->callback(function (array $params): bool {
                    return isset($params['headers']) &&
                           is_array($params['headers']) &&
                           isset($params['headers']['Authorization']) &&
                           $params['headers']['Authorization'] === 'Bearer test-api-key' &&
                           isset($params['body']) &&
                           is_array($params['body']) &&
                           isset($params['body']['file']) &&
                           isset($params['body']['purpose']) &&
                           $params['body']['purpose'] === 'assistants';
                })
            )
            ->willReturn($response);

        $result = $this->fileApi->upload($filePath);
        $this->assertEquals('file-123', $result);

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testUploadWithNonExistentFile(): void
    {
        $filePath = sys_get_temp_dir() . '/non-existent-file.txt';

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('File not found: ' . $filePath);

        $this->fileApi->upload($filePath);
    }

    public function testUploadWithUnreadableFile(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test-file-') . '.txt';
        file_put_contents($filePath, 'test content');

        unlink($filePath);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('File not found: ' . $filePath);

        $this->fileApi->upload($filePath);
    }

    public function testDeleteFile(): void
    {
        $fileId = 'file-123';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'DELETE',
                "https://api.openai.com/v1/files/{$fileId}",
                $this->callback(function (array $params): bool {
                    return isset($params['headers']) &&
                           is_array($params['headers']) &&
                           isset($params['headers']['Authorization']) &&
                           $params['headers']['Authorization'] === 'Bearer test-api-key';
                })
            )
            ->willReturn($response);

        $this->fileApi->delete($fileId);
        $this->addToAssertionCount(1);
    }

    public function testListFiles(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn(
            '{"data": [{"id": "file-1", "name": "test1.txt"}, {"id": "file-2", "name": "test2.txt"}]}'
        );

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.openai.com/v1/files',
                $this->callback(function (array $params): bool {
                    return isset($params['headers']) &&
                           is_array($params['headers']) &&
                           isset($params['headers']['Authorization']) &&
                           $params['headers']['Authorization'] === 'Bearer test-api-key';
                })
            )
            ->willReturn($response);

        $result = $this->fileApi->list();
        $this->assertCount(2, $result);
        $this->assertEquals('file-1', $result[0]['id']);
        $this->assertEquals('test1.txt', $result[0]['name']);
    }

    public function testListFilesWithEmptyData(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn('{"data": null}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $result = $this->fileApi->list();
        $this->assertEmpty($result);
    }

    public function testGetFile(): void
    {
        $fileId = 'file-123';
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getContent')->willReturn('{"id": "file-123", "name": "test.txt", "size": 1024}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                "https://api.openai.com/v1/files/{$fileId}",
                $this->callback(function (array $params): bool {
                    return isset($params['headers']) &&
                           is_array($params['headers']) &&
                           isset($params['headers']['Authorization']) &&
                           $params['headers']['Authorization'] === 'Bearer test-api-key';
                })
            )
            ->willReturn($response);

        $result = $this->fileApi->get($fileId);
        $this->assertEquals('file-123', $result['id']);
        $this->assertEquals('test.txt', $result['name']);
        $this->assertEquals(1024, $result['size']);
    }

    public function testHandleResponseWithUnauthorized(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);
        $response->method('getContent')->willReturn('{"error": "Unauthorized"}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(UnhautorizeKeyException::class);
        $this->expectExceptionMessage('Unauthorized: Invalid or missing API key.');

        $this->fileApi->list();
    }

    public function testHandleResponseWithBadRequest(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);
        $response->method('getContent')->willReturn('{"error": "Bad Request"}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Bad Request: The request was invalid or cannot be processed.');

        $this->fileApi->list();
    }

    public function testHandleResponseWithTooManyRequests(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(429);
        $response->method('getContent')->willReturn('{"error": "Rate limit exceeded"}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(TooManyRequestException::class);
        $this->expectExceptionMessage('Too Many Requests: You have exceeded your request quota.');

        $this->fileApi->list();
    }

    public function testHandleResponseWithOtherError(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getContent')->willReturn('{"error": "Internal Server Error"}');

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $this->expectException(OtherException::class);
        $this->expectExceptionMessage(
            'Unexpected error: received HTTP status code 500. {"error": "Internal Server Error"}'
        );

        $this->fileApi->list();
    }
}
