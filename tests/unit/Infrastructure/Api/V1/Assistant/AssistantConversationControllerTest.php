<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\AssistantConversation\AssistantConversation;
use Chatbot\Application\Service\AssistantConversation\AssistantConversationRequest;
use Chatbot\Application\Service\AssistantConversation\AssistantConversationResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\AssistantConversationController;
use Chatbot\Infrastructure\Exception\AssistantMessageNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Safe\json_encode;

class AssistantConversationControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $this->assertInstanceOf(AssistantConversationController::class, $controller);
    }

    public function testAssistantConversationControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('ast-123'));

        $request = Request::create(
            "/api/v1/assistant/conversation",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ast_id" => "ast-123",
                "message" => "Hello"
            ])
        );

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        $entityManager->method('find')->willReturn($assistant);
        $entityManager->method('flush');

        $serviceResponse = $this->createMock(AssistantConversationResponse::class);
        $assistantConversationService->method('getResponse')->willReturn($serviceResponse);

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $response = $controller->assistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testAssistantConversationWithValidRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('ast-123'));

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "ast-123", "message": "Hello"}');

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        $entityManager->method('find')->willReturn($assistant);
        $entityManager->method('flush');

        $serviceResponse = $this->createMock(AssistantConversationResponse::class);
        $assistantConversationService->method('getResponse')->willReturn($serviceResponse);

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $response = $controller->assistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
    }


    public function testAssistantConversationWithException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "", "message": "Hello"}');

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $response = $controller->assistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testBuildAssistantConversationRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('ast-123'));

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "ast-123", "message": "Hello"}');

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        $entityManager->method('find')->willReturn($assistant);

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildAssistantConversationRequest');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $request);

        $this->assertInstanceOf(AssistantConversationRequest::class, $result);
    }

    public function testBuildAssistantConversationRequestWithEmptyMessage(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "ast-123", "message": ""}');

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildAssistantConversationRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le message ne peut pas être vide");

        $method->invoke($controller, $request);
    }

    public function testBuildAssistantConversationRequestWithEmptyAssistantId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "", "message": "Hello"}');

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildAssistantConversationRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de l'assistant est requis");

        $method->invoke($controller, $request);
    }

    public function testBuildAssistantConversationRequestWithAssistantNotFound(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "ast-123", "message": "Hello"}');

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        $entityManager->method('find')->willReturn(null);

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildAssistantConversationRequest');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Assistant non trouvé: ast-123");

        $method->invoke($controller, $request);
    }

    public function testAssistantConversationServiceExecutionAndFlush(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('ast-123'));

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "ast-123", "message": "Hello"}');

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        $entityManager->method('find')->willReturn($assistant);
        $entityManager->expects($this->once())->method('flush');

        $serviceResponse = $this->createMock(AssistantConversationResponse::class);
        $assistantConversationService->expects($this->once())->method('execute');
        $assistantConversationService->expects($this->once())->method('getResponse')->willReturn($serviceResponse);

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $response = $controller->assistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testAssistantConversationWithAssistantMessageNotFoundException(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $assistantConversationService = $this->createMock(AssistantConversation::class);

        $assistant = $this->createMock(Assistant::class);
        $assistant->method('getAssistantId')->willReturn(new AssistantId('ast-123'));

        $request = $this->createMock(Request::class);
        $request->method('getContent')->willReturn('{"ast_id": "ast-123", "message": "Hello"}');

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        $entityManager->method('find')->willReturn($assistant);

        $assistantConversationService
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new AssistantMessageNotFoundException("Aucun message de l'assistant trouvé"));

        $controller = new AssistantConversationController($entityManager, $assistantConversationService);

        $response = $controller->assistantConversation($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);

        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertArrayHasKey('error_message', $responseData);
        $this->assertEquals('AssistantMessageNotFoundException', $responseData['error']);
        $this->assertEquals("Aucun message de l'assistant trouvé", $responseData['error_message']);
    }
}
