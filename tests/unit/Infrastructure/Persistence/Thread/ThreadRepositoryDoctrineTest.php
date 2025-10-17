<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use Chatbot\Infrastructure\Exception\ThreadNotFoundException;
use Chatbot\Infrastructure\Persistence\Thread\ThreadRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class ThreadRepositoryDoctrineTest extends TestCase
{
    public function testAddCallsPersist(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Thread::class));

        $repository = new ThreadRepositoryDoctrine($em);

        $thread = new Thread(
            new ThreadId('test-thread-id'),
            new AssistantId('test-assistant-id'),
            'external-thread-123',
            new ConversationId('test-conversation-id')
        );

        $repository->add($thread);
    }

    public function testFindByIdCallsFind(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $threadId = new ThreadId('test-thread-id');

        $em->expects($this->once())
            ->method('find')
            ->with('Chatbot\Domain\Model\Thread\Thread', $threadId)
            ->willReturn(null);

        $repository = new ThreadRepositoryDoctrine($em);

        $result = $repository->findById($threadId);

        $this->assertNull($result);
    }

    public function testFindByAssistantIdCallsGetRepositoryAndFindBy(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        $repository = $this->createMock(EntityRepository::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $assistantId = new AssistantId('test-assistant-id');

        $em->expects($this->once())
            ->method('getRepository')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('findBy')
            ->with(['assistantId' => $assistantId])
            ->willReturn([]);

        $threadRepository = new ThreadRepositoryDoctrine($em);

        $result = $threadRepository->findByAssistantId($assistantId);

        $this->assertEmpty($result);
    }

    public function testFindByConversationIdCallsGetRepositoryAndFindOneBy(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        $repository = $this->createMock(EntityRepository::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $conversationId = new ConversationId('test-conversation-id');

        $em->expects($this->once())
            ->method('getRepository')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['conversationId' => $conversationId])
            ->willReturn(null);

        $threadRepository = new ThreadRepositoryDoctrine($em);

        $result = $threadRepository->findByConversationId($conversationId);

        $this->assertNull($result);
    }

    public function testFindAllCallsGetRepositoryAndFindAll(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);
        $repository = $this->createMock(EntityRepository::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $em->expects($this->once())
            ->method('getRepository')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $threadRepository = new ThreadRepositoryDoctrine($em);

        $result = $threadRepository->findAll();

        $this->assertEmpty($result);
    }

    public function testDeleteCallsFindByIdAndRemove(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $threadId = new ThreadId('test-thread-id');
        $thread = new Thread(
            $threadId,
            new AssistantId('test-assistant-id'),
            'external-thread-123',
            new ConversationId('test-conversation-id')
        );

        $em->expects($this->once())
            ->method('find')
            ->with('Chatbot\Domain\Model\Thread\Thread', $threadId)
            ->willReturn($thread);

        $em->expects($this->once())
            ->method('remove')
            ->with($thread);

        $threadRepository = new ThreadRepositoryDoctrine($em);

        $threadRepository->delete($threadId);
    }

    public function testDeleteThrowsExceptionWhenThreadNotFound(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $classMetadata = $this->createMock(ClassMetadata::class);

        $classMetadata->name = 'Chatbot\Domain\Model\Thread\Thread';

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('Chatbot\Domain\Model\Thread\Thread')
            ->willReturn($classMetadata);

        $threadId = new ThreadId('non-existent-thread-id');

        $em->expects($this->once())
            ->method('find')
            ->with('Chatbot\Domain\Model\Thread\Thread', $threadId)
            ->willReturn(null);

        $threadRepository = new ThreadRepositoryDoctrine($em);

        $this->expectException(ThreadNotFoundException::class);
        $this->expectExceptionMessage("Le thread avec l'id = $threadId n'a pas été trouvé");

        $threadRepository->delete($threadId);
    }
}
