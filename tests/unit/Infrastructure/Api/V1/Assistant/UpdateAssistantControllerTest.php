<?php

namespace Chatbot\Tests\Unit\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\UpdateAssistant\UpdateAssistant;
use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantRequest;
use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\Assistant\UpdateAssistantController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAssistantControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController(
            $entityManager,
            $updateAssistantService
        );

        $this->assertInstanceOf(UpdateAssistantController::class, $controller);
    }

    public function testUpdateAssistantControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController(
            $entityManager,
            $updateAssistantService
        );

        $request = Request::create(
            uri: '/api/v1/assistant/update/test-assistant-id',
            method: 'PUT',
            content: json_encode([
                'name'        => 'Patrick',
                'instruction' => 'Tu es le meilleur assistant',
            ], JSON_THROW_ON_ERROR)
        );

        $updateAssistantService
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(UpdateAssistantRequest::class));

        $updateAssistantService
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(new UpdateAssistantResponse(
                new AssistantId('test-assistant-id'),
                'Patrick',
                'Tu es le meilleur assistant'
            ));

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $response = $controller->updateAssistant('test-assistant-id', $request);
        //

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testUpdateAssistantControllerExecuteWithUnSuccess(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController($entityManager, $updateAssistantService);

        $request = Request::create(
            uri: '/api/v1/assistant/update/test-assistant-id',
            method: 'PUT',
            content: json_encode([
            'name'        => 'Patrick',
            'instruction' => 'Tu es le meilleur assistant',
            ], JSON_THROW_ON_ERROR)
        );

        $updateAssistantService
        ->expects($this->once())
        ->method('execute')
        ->with($this->isInstanceOf(UpdateAssistantRequest::class))
        ->willThrowException(new \RuntimeException('Update failed'));

        $updateAssistantService->expects($this->never())->method('getResponse');
        $entityManager->expects($this->never())->method('flush');

        $response = $controller->updateAssistant('test-assistant-id', $request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(500, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertIsString($content); // ✅ élimine string|false

    /** @var array{success: bool, error: string, error_message: string} $payload */
        $payload = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertFalse($payload['success']);
        $this->assertSame('RuntimeException', $payload['error']);
        $this->assertSame('Update failed', $payload['error_message']);
    }





    public function testBuildUpdateAssistantRequestWithValidData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController(
            $entityManager,
            $updateAssistantService
        );

        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('buildUpdateAssistantRequest');
        $method->setAccessible(true);

        $assistantId = 'test-assistant-id';
        $name = 'Patrick';
        $instruction = 'Tu es le meilleur assistant';

        $request = Request::create(
            uri: '',
            method: 'PUT',
            content: json_encode([
            'name'        => $name,
            'instruction' => $instruction,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $controller->updateAssistant($assistantId, $request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testBuildUpdateAssistantRequestWithNoId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController($entityManager, $updateAssistantService);

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildUpdateAssistantRequest');
        $method->setAccessible(true);

        $request = Request::create(
            uri: '',
            method: 'PUT',
            content: json_encode([
                'name' => 'Patrick',
                'instruction' => 'Tu es le meilleur assistant',
            ], JSON_THROW_ON_ERROR)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'ID de l'assistant est requis");

        $method->invoke($controller, '', $request);
    }

    public function testBuildUpdateAssistantRequestCastsScalarValuesToString(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController($entityManager, $updateAssistantService);

        $method = (new \ReflectionClass($controller))->getMethod('buildUpdateAssistantRequest');
        $method->setAccessible(true);

        $request = Request::create(
            uri: '',
            method: 'PUT',
            content: json_encode([
                'name' => 123,         // int -> "123"
                'instruction' => true, // bool -> "1"
            ], JSON_THROW_ON_ERROR)
        );

        /** @var UpdateAssistantRequest $dto */
        $dto = $method->invoke($controller, 'assistant-123', $request);

        $dtoRef = new \ReflectionClass($dto);
        $values = [];
        foreach ($dtoRef->getProperties() as $prop) {
            $prop->setAccessible(true);
            $values[] = $prop->getValue($dto);
        }

        $this->assertContains('123', $values);
        $this->assertContains('1', $values);
    }

    public function testBuildUpdateAssistantRequestWithExceptionOnName(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController($entityManager, $updateAssistantService);

        $method = (new \ReflectionClass($controller))->getMethod('buildUpdateAssistantRequest');
        $method->setAccessible(true);

        $request = Request::create(
            uri: '',
            method: 'PUT',
            content: json_encode([
                'name' => ['bad'],
            ], JSON_THROW_ON_ERROR)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Le nom de l'assistant doit être une chaîne de caractères");

        $method->invoke($controller, 'assistant-123', $request);
    }

    public function testBuildUpdateAssistantRequestWithExceptionOnInstruction(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantService = $this->createMock(UpdateAssistant::class);

        $controller = new UpdateAssistantController($entityManager, $updateAssistantService);

        $method = (new \ReflectionClass($controller))->getMethod('buildUpdateAssistantRequest');
        $method->setAccessible(true);

        $request = Request::create(
            uri: '',
            method: 'PUT',
            content: json_encode([
                'instruction' => [true],
            ], JSON_THROW_ON_ERROR)
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'instruction doit être une chaîne de caractères");

        $method->invoke($controller, 'assistant-123', $request);
    }
}
