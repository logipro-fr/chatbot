<?php

namespace Chatbot\Tests\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFiles;
use Chatbot\Application\Service\DeleteAssistantFiles\DeleteAssistantFilesRequest;
use Chatbot\Infrastructure\Api\V1\Assistant\DeleteAssistantFileController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteAssistantFileControllerTest extends TestCase
{
    public function testConstructor(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $updateAssistantFilesService = $this->createMock(DeleteAssistantFiles::class);

        $controller = new DeleteAssistantFileController(
            $entityManager,
            $updateAssistantFilesService
        );

        $this->assertInstanceOf(DeleteAssistantFileController::class, $controller);
    }

    public function testUpdateAssistantFilesControllerExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $deleteAssistantFileService = $this->createMock(DeleteAssistantFiles::class);

        $controller = new DeleteAssistantFileController(
            $entityManager,
            $deleteAssistantFileService
        );

        $deleteAssistantFileService
           ->expects($this->once())
           ->method('execute')
           ->with($this->isInstanceOf(DeleteAssistantFilesRequest::class));

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
        $detachAssistantFileService = $this->createMock(DeleteAssistantFiles::class);

        $controller = new DeleteAssistantFileController($entityManager, $detachAssistantFileService);

        $detachAssistantFileService->expects($this->never())->method('execute');
        $entityManager->expects($this->never())->method('flush');

        $response = $controller->deleteAssistantFile('', 'fil_123475869');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotSame(400, $response->getStatusCode());
    }
}
