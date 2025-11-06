<?php

namespace Chatbot\Tests\Unit\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversation;
use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationRequest;
use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationResponse;
use Chatbot\Infrastructure\Api\V1\Assistant\ContinueAssistantConversationController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContinueAssistantConversationControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $this->assertInstanceOf(ContinueAssistantConversationController::class, $controller);
    }

    public function testContinueAssistantConversationControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => 'test-conversation-id',
            'message' => 'Test message'
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ContinueAssistantConversationRequest::class));

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new ContinueAssistantConversationResponse('test-conversation-id', 'Test response'));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->continueAssistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testContinueAssistantConversationWithValidRequest(): void
    {
        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => 'test-conversation-id',
            'message' => 'Test message'
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('execute');

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new ContinueAssistantConversationResponse('test-conversation-id', 'Test response'));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->continueAssistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testContinueAssistantConversationWithException(): void
    {
        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => 'test-conversation-id',
            'message' => 'Test message'
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Test exception'));

        $response = $controller->continueAssistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildContinueAssistantConversationRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => 'test-conversation-id',
            'message' => 'Test message'
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildContinueAssistantConversationRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request);

        $this->assertInstanceOf(ContinueAssistantConversationRequest::class, $result);
    }

    public function testBuildContinueAssistantConversationRequestWithEmptyMessage(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => 'test-conversation-id',
            'message' => ''
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildContinueAssistantConversationRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le message ne peut pas être vide");

        $method->invoke($controller, $request);
    }

    public function testBuildContinueAssistantConversationRequestWithEmptyConversationId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => '',
            'message' => 'Test message'
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildContinueAssistantConversationRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de la conversation est requis");

        $method->invoke($controller, $request);
    }

    public function testContinueAssistantConversationServiceExecutionAndFlush(): void
    {
        $request = new Request();
        $jsonContent = json_encode([
            'conversation_id' => 'test-conversation-id',
            'message' => 'Test message'
        ]);
        assert($jsonContent !== false);
        $request->initialize([], [], [], [], [], [], $jsonContent);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $continueAssistantConversationService = $this->createMock(ContinueAssistantConversation::class);

        $controller = new ContinueAssistantConversationController(
            $entityManager,
            $continueAssistantConversationService
        );

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ContinueAssistantConversationRequest::class));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $continueAssistantConversationService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new ContinueAssistantConversationResponse('test-conversation-id', 'Test response'));

        $response = $controller->continueAssistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
