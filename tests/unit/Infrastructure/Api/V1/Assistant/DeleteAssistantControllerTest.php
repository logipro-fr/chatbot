<?php

namespace Chatbot\Tests\Unit\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistant;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantRequest;
use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\DeleteAssistantController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

class DeleteAssistantControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $this->assertInstanceOf(DeleteAssistantController::class, $controller);
    }

    public function testDeleteAssistantControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $deleteAssistantService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(DeleteAssistantRequest::class));

        $deleteAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new DeleteAssistantResponse(new AssistantId('test-assistant-id'), 'external-asst-123'));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->deleteAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testDeleteAssistantWithValidRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $deleteAssistantService
            ->expects($this->once())
            ->method('execute');

        $deleteAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new DeleteAssistantResponse(new AssistantId('test-assistant-id'), 'external-asst-123'));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->deleteAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteAssistantWithException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $deleteAssistantService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Test exception'));

        $response = $controller->deleteAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildDeleteAssistantRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildDeleteAssistantRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'test-assistant-id');

        $this->assertInstanceOf(DeleteAssistantRequest::class, $result);
    }

    public function testBuildDeleteAssistantRequestWithEmptyAssistantId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildDeleteAssistantRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de l'assistant est requis");

        $method->invoke($controller, '');
    }

    public function testDeleteAssistantServiceExecutionAndFlush(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantService = $this->createMock(DeleteAssistant::class);

        $controller = new DeleteAssistantController(
            $entityManager,
            $deleteAssistantService
        );

        $deleteAssistantService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(DeleteAssistantRequest::class));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $deleteAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new DeleteAssistantResponse(new AssistantId('test-assistant-id'), 'external-asst-123'));

        $response = $controller->deleteAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
