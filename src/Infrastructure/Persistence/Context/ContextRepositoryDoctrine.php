<?php

namespace Chatbot\Infrastructure\Persistence\Context;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<Context>*/

class ContextRepositoryDoctrine extends EntityRepository implements ContextRepositoryInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $class = $em->getClassMetadata(Context::class);
        parent::__construct($em, $class);
    }

    public function add(Context $context): void
    {
        $this->getEntityManager()->persist($context);
    }

    public function findById(ContextId $contextId): Context
    {
        $context = $this->getEntityManager()->find(Context::class, $contextId);
        if ($context === null) {
            throw new ContextNotFoundException("the context with id = $contextId is not found");
        }
        return $context;
    }

    public function removeContext(ContextId $context): void
    {
        $context = $this->findById($context);
        $this->getEntityManager()->remove($context);
    }
}
