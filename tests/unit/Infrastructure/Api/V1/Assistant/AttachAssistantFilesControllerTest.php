<?php

namespace Chatbot\Tests\Unit\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFiles;
use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFilesRequest;
use Chatbot\Application\Service\AttachAssistantFiles\AttachAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\AttachAssistantFilesController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachAssistantFilesControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $this->assertInstanceOf(AttachAssistantFilesController::class, $controller);
    }

    public function testUpdateAssistantFilesControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 'file-2']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(AttachAssistantFilesRequest::class));

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new AttachAssistantFilesResponse(
                new AssistantId('test-assistant-id'),
                ['file-1', 'file-2'],
                'vs_123456'
            ));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->attachAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testUpdateAssistantFilesWithValidRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 'file-2', 'file-3']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('execute');

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(
                new AttachAssistantFilesResponse(
                    new AssistantId('test-assistant-id'),
                    ['file-1', 'file-2', 'file-3'],
                    'vs_123456'
                )
            );

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->attachAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateAssistantFilesWithException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Test exception'));

        $response = $controller->attachAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildUpdateFilesRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 'file-2']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildAttachFilesRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request, 'test-assistant-id');

        $this->assertInstanceOf(AttachAssistantFilesRequest::class, $result);
    }

    public function testBuildUpdateFilesRequestWithEmptyAssistantId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildAttachFilesRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de l'assistant est requis");

        $method->invoke($controller, $request, '');
    }

    public function testBuildUpdateFilesRequestWithMixedFileIds(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 123, 'file-2', true]
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildAttachFilesRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request, 'test-assistant-id');

        $this->assertInstanceOf(AttachAssistantFilesRequest::class, $result);
    }

    public function testUpdateAssistantFilesServiceExecutionAndFlush(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $attachAssistantFilesService = $this->createMock(AttachAssistantFiles::class);

        $controller = new AttachAssistantFilesController(
            $entityManager,
            $attachAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(AttachAssistantFilesRequest::class));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $attachAssistantFilesService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new AttachAssistantFilesResponse(
                new AssistantId('test-assistant-id'),
                ['file-1'],
                'vs_123456'
            ));

        $response = $controller->attachAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
