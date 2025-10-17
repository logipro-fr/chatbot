<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\CreateAssistantFromContextController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_encode;

class CreateAssistantFromContextControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $this->assertInstanceOf(CreateAssistantFromContextController::class, $controller);
    }

    public function testCreateAssistantFromContextControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = Request::create(
            "/api/v1/assistant/create-from-context",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "context_id" => "ctx-123",
                "file_ids" => ["file-1", "file-2"]
            ])
        );

        $entityManager->method('flush');

        $serviceResponse = $this->createMock(CreateAssistantFromContextResponse::class);
        $createAssistantFromContextService->method('getResponse')->willReturn($serviceResponse);

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $response = $controller->createAssistantFromContext($request);

        $this->assertInstanceOf(Response::class, $response);
        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testCreateAssistantFromContextWithValidRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": "ctx-123", "file_ids": ["file-1", "file-2"]}');

        $entityManager->method('flush');

        $serviceResponse = $this->createMock(CreateAssistantFromContextResponse::class);
        $createAssistantFromContextService->method('getResponse')->willReturn($serviceResponse);

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $response = $controller->createAssistantFromContext($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCreateAssistantFromContextWithException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": "", "file_ids": []}');

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $response = $controller->createAssistantFromContext($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildCreateAssistantRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": "ctx-123", "file_ids": ["file-1", "file-2"]}');

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildCreateAssistantRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request);

        $this->assertInstanceOf(CreateAssistantFromContextRequest::class, $result);
    }

    public function testBuildCreateAssistantRequestWithEmptyContextId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": "", "file_ids": []}');

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildCreateAssistantRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID du contexte est requis");

        $method->invoke($controller, $request);
    }

    public function testBuildCreateAssistantRequestWithNonStringContextId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": 123, "file_ids": []}');

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildCreateAssistantRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Context ID must be a string');

        $method->invoke($controller, $request);
    }

    public function testBuildCreateAssistantRequestWithMixedFileIds(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": "ctx-123", "file_ids": ["file-1", 123, "file-2"]}');

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildCreateAssistantRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request);

        $this->assertInstanceOf(CreateAssistantFromContextRequest::class, $result);
    }

    public function testCreateAssistantFromContextServiceExecutionAndFlush(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $createAssistantFromContextService = $this->createMock(CreateAssistantFromContext::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"context_id": "ctx-123", "file_ids": ["file-1"]}');

        $entityManager->expects($this->once())->method('flush');

        $serviceResponse = $this->createMock(CreateAssistantFromContextResponse::class);
        $createAssistantFromContextService->expects($this->once())->method('execute');
        $createAssistantFromContextService->expects($this->once())->method('getResponse')->willReturn($serviceResponse);

        $controller = new CreateAssistantFromContextController($entityManager, $createAssistantFromContextService);

        $response = $controller->createAssistantFromContext($request);

        $this->assertInstanceOf(Response::class, $response);
    }
}
