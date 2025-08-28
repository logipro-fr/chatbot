<?php

namespace Chatbot\Infrastructure\Persistence\Thread;

use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;
use Chatbot\Infrastructure\Exception\ThreadNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<Thread>*/
class ThreadRepositoryDoctrine extends EntityRepository implements ThreadRepositoryInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $class = $em->getClassMetadata(Thread::class);
        parent::__construct($em, $class);
    }

    public function add(Thread $thread): void
    {
        $this->getEntityManager()->persist($thread);
    }

    public function findById(ThreadId $threadId): ?Thread
    {
        return $this->getEntityManager()->find(Thread::class, $threadId);
    }

    public function findByAssistantId(AssistantId $assistantId): array
    {
        return $this->getEntityManager()
            ->getRepository(Thread::class)
            ->findBy(['assistantId' => $assistantId]);
    }

    public function findAll(): array
    {
        return $this->getEntityManager()->getRepository(Thread::class)->findAll();
    }

    public function delete(ThreadId $threadId): void
    {
        $thread = $this->findById($threadId);
        if ($thread === null) {
            throw new ThreadNotFoundException("Le thread avec l'id = $threadId n'a pas été trouvé");
        }
        $this->getEntityManager()->remove($thread);
    }
}
