<?php

namespace Chatbot\Tests\Unit\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\GetAssistant\GetAssistant;
use Chatbot\Application\Service\GetAssistant\GetAssistantRequest;
use Chatbot\Application\Service\GetAssistant\GetAssistantResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\GetAssistantController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

class GetAssistantControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $this->assertInstanceOf(GetAssistantController::class, $controller);
    }

    public function testGetAssistantControllerExecute(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('test-assistant-id'));
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('external-asst-123');
        $assistant->method('getFileIds')->willReturn(['file-1', 'file-2']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $getAssistantService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetAssistantRequest::class));

        $getAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new GetAssistantResponse($assistant, ['external' => 'data']));

        $response = $controller->getAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testGetAssistantWithValidRequest(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('test-assistant-id'));
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('external-asst-123');
        $assistant->method('getFileIds')->willReturn(['file-1']);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $getAssistantService
            ->expects($this->once())
            ->method('execute');

        $getAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new GetAssistantResponse($assistant, null));

        $response = $controller->getAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAssistantWithException(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $getAssistantService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Test exception'));

        $response = $controller->getAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testGetAssistantWithNullResponse(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $getAssistantService
            ->expects($this->once())
            ->method('execute');

        $getAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(null);

        $response = $controller->getAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildGetAssistantRequestWithValidData(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildGetAssistantRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'test-assistant-id');

        $this->assertInstanceOf(GetAssistantRequest::class, $result);
    }

    public function testBuildGetAssistantRequestWithEmptyAssistantId(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildGetAssistantRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de l'assistant est requis");

        $method->invoke($controller, '');
    }

    public function testGetAssistantServiceExecutionAndResponse(): void
    {
        $getAssistantService = $this->createMock(GetAssistant::class);

        $controller = new GetAssistantController(
            $getAssistantService
        );

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('test-assistant-id'));
        $assistant->method('getName')->willReturn('Test Assistant');
        $assistant->method('getInstructions')->willReturn('Test instructions');
        $assistant->method('getExternalAssistantId')->willReturn('external-asst-123');
        $assistant->method('getFileIds')->willReturn([]);
        $assistant->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2023-01-01 12:00:00'));

        $getAssistantService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetAssistantRequest::class));

        $getAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new GetAssistantResponse($assistant, null));

        $response = $controller->getAssistant('test-assistant-id');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
