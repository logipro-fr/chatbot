<?php

namespace Chatbot\Tests\Unit\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFiles;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\UpdateAssistantFilesController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAssistantFilesControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $this->assertInstanceOf(UpdateAssistantFilesController::class, $controller);
    }

    public function testUpdateAssistantFilesControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 'file-2']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(UpdateAssistantFilesRequest::class));

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new UpdateAssistantFilesResponse(
                new AssistantId('test-assistant-id'),
                ['file-1', 'file-2'],
                'vs_123456'
            ));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->updateAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testUpdateAssistantFilesWithValidRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 'file-2', 'file-3']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('execute');

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(
                new UpdateAssistantFilesResponse(
                    new AssistantId('test-assistant-id'),
                    ['file-1', 'file-2', 'file-3'],
                    'vs_123456'
                )
            );

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->updateAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateAssistantFilesWithException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Test exception'));

        $response = $controller->updateAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildUpdateFilesRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 'file-2']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildUpdateFilesRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request, 'test-assistant-id');

        $this->assertInstanceOf(UpdateAssistantFilesRequest::class, $result);
    }

    public function testBuildUpdateFilesRequestWithEmptyAssistantId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildUpdateFilesRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de l'assistant est requis");

        $method->invoke($controller, $request, '');
    }

    public function testBuildUpdateFilesRequestWithMixedFileIds(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1', 123, 'file-2', true]
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildUpdateFilesRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request, 'test-assistant-id');

        $this->assertInstanceOf(UpdateAssistantFilesRequest::class, $result);
    }

    public function testUpdateAssistantFilesServiceExecutionAndFlush(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(UpdateAssistantFiles::class);

        $controller = new UpdateAssistantFilesController(
            $entityManager,
            $updateAssistantFilesService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'file_ids' => ['file-1']
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(UpdateAssistantFilesRequest::class));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $updateAssistantFilesService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new UpdateAssistantFilesResponse(
                new AssistantId('test-assistant-id'),
                ['file-1'],
                'vs_123456'
            ));

        $response = $controller->updateAssistantFiles($request, 'test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
