<?php

namespace Chatbot\Infrastructure\Persistence\Context;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\Exception\NoIdException;
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

    public function findById(ContextId $contextId): Context|false
    {
        $context = $this->getEntityManager()->find(Context::class, $contextId);
        if ($context === null) {
            throw new NoIdException("$contextId is not found in Data Base");
        }
        return $context;
    }
}
