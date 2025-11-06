<?php

namespace Chatbot\Infrastructure\Persistence\Assistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Infrastructure\Exception\AssistantNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<Assistant>*/
class AssistantRepositoryDoctrine extends EntityRepository implements AssistantRepositoryInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $class = $em->getClassMetadata(Assistant::class);
        parent::__construct($em, $class);
    }

    public function add(Assistant $assistant): void
    {
        $this->getEntityManager()->persist($assistant);
    }

    public function findById(AssistantId $assistantId): ?Assistant
    {
        return $this->getEntityManager()->find(Assistant::class, $assistantId);
    }

    public function findAll(): array
    {
        return $this->getEntityManager()->getRepository(Assistant::class)->findAll();
    }

    public function delete(AssistantId $assistantId): void
    {
        $assistant = $this->findById($assistantId);
        if ($assistant === null) {
            throw new AssistantNotFoundException("L'assistant avec l'id = $assistantId n'a pas été trouvé");
        }
        $this->getEntityManager()->remove($assistant);
    }
}
