<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFiles;
use Chatbot\Application\Service\DetachAssistantFiles\DetachAssistantFilesRequest;
use Chatbot\Infrastructure\Api\V1\Assistant\DetachAssistantFileController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DetachAssistantFileControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $detachAssistantFileService = $this->createMock(DetachAssistantFiles::class);

        $controller = new DetachAssistantFileController(
            $entityManager,
            $detachAssistantFileService
        );

        $this->assertInstanceOf(DetachAssistantFileController::class, $controller);
    }

    public function testDetachAssistantFilesControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $detachAssistantFilesService = $this->createMock(DetachAssistantFiles::class);

        $controller = new DetachAssistantFileController(
            $entityManager,
            $detachAssistantFilesService
        );

        $detachAssistantFilesService
           ->expects($this->once())
           ->method('execute')
           ->with($this->isInstanceOf(DetachAssistantFilesRequest::class));

        $entityManager
           ->expects($this->once())
           ->method('flush');

        $response = $controller->deleteAssistantFile('test_assistant_id', 'fil_123475869');

        $this->assertEquals(200, $response->getStatusCode());

        $responseContent = $response->getContent();
        $this->assertIsString($responseContent);
        $this->assertJson($responseContent);
    }

    public function testBuildDetachAssistantRequest(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $detachAssistantFileService = $this->createMock(DetachAssistantFiles::class);

        $controller = new DetachAssistantFileController($entityManager, $detachAssistantFileService);

        $detachAssistantFileService->expects($this->once())->method('execute');
        $entityManager->expects($this->once())->method('flush');

        $response = $controller->deleteAssistantFile('test_assistant_id', 'fil_123475869');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testBuildDetachAssistantRequestWithNoAssistantId(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $detachAssistantFileService = $this->createMock(DetachAssistantFiles::class);

        $controller = new DetachAssistantFileController($entityManager, $detachAssistantFileService);

        $detachAssistantFileService->expects($this->never())->method('execute');
        $entityManager->expects($this->never())->method('flush');

        $response = $controller->deleteAssistantFile('', 'fil_123475869');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }
}
