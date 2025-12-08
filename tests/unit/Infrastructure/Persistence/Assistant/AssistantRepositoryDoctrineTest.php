<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Assistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Exception\AssistantNotFoundException;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class AssistantRepositoryDoctrineTest extends TestCase
{
    public function testConstructor(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $repository = new AssistantRepositoryDoctrine($em);

        $this->assertInstanceOf(AssistantRepositoryDoctrine::class, $repository);
    }

    public function testAddCallsPersist(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Assistant::class));

        $repository = new AssistantRepositoryDoctrine($em);

        $assistant = $this->createMock(Assistant::class);
        $repository->add($assistant);
    }

    public function testFindByIdCallsFind(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $assistantId = new AssistantId('test-assistant-id');

        $em->expects($this->once())
            ->method('find')
            ->with('Chatbot\Domain\Model\Assistant\Assistant', $assistantId)
            ->willReturn(null);

        $repository = new AssistantRepositoryDoctrine($em);

        $result = $repository->findById($assistantId);

        $this->assertNull($result);
    }

    public function testFindAllCallsGetRepositoryAndFindAll(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        $repository = $this->createMock(EntityRepository::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $em->expects($this->once())
            ->method('getRepository')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $assistantRepository = new AssistantRepositoryDoctrine($em);

        $result = $assistantRepository->findAll();

        $this->assertEmpty($result);
    }

    public function testDeleteCallsFindByIdAndRemove(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $assistantId = new AssistantId('test-assistant-id');
        $assistant = $this->createMock(Assistant::class);

        $em->expects($this->once())
            ->method('find')
            ->with('Chatbot\Domain\Model\Assistant\Assistant', $assistantId)
            ->willReturn($assistant);

        $em->expects($this->once())
            ->method('remove')
            ->with($assistant);

        $assistantRepository = new AssistantRepositoryDoctrine($em);

        $assistantRepository->delete($assistantId);
    }

    public function testDeleteThrowsExceptionWhenAssistantNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $assistantId = new AssistantId('non-existent-assistant-id');

        $em->expects($this->once())
            ->method('find')
            ->with('Chatbot\Domain\Model\Assistant\Assistant', $assistantId)
            ->willReturn(null);

        $assistantRepository = new AssistantRepositoryDoctrine($em);

        $this->expectException(AssistantNotFoundException::class);
        $this->expectExceptionMessage("L'assistant avec l'id = $assistantId n'a pas été trouvé");

        $assistantRepository->delete($assistantId);
    }

    public function testAssistantAddandGetVectorId(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Assistant\Assistant';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Assistant\Assistant')
            ->willReturn($classMetadata);

        $assistantRepository = new AssistantRepositoryDoctrine($em);
        $assistantId = new AssistantId('test-assistant-id');
        $assistant = new Assistant(
            $assistantId,
            'Test Assistant',
            'Test instructions',
            'external-assistant-id',
            []
        );

        $assistant->setVectorId('vector-12345');
        $this->assertEquals('vector-12345', $assistant->getVectorId());

        $assistantRepository->add($assistant);
        $retrievedAssistant = $assistantRepository->findById($assistantId);
        $this->assertEquals('vector-12345', $retrievedAssistant->getVectorId());

    }
}
