<?php

namespace Chatbot\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/** @extends EntityRepository<Conversation>*/

class ConversationRepositoryDoctrine extends EntityRepository implements ConversationRepositoryInterface
{
    public function __construct(EntityManagerInterface $em)
    {
        $class = $em->getClassMetadata(Conversation::class);
        parent::__construct($em, $class);
    }

    public function add(Conversation $conversation): void
    {
        $this->getEntityManager()->persist($conversation);
    }

    public function findById(ConversationId $conversationId): Conversation
    {
        $conversation = $this->getEntityManager()->find(Conversation::class, $conversationId);
        if ($conversation === null) {
            throw new ConversationNotFoundException(sprintf("Conversation '%s' not found", $conversationId));
        }
        return $conversation;
    }

    public function findByContextId(ContextId $contextId): Conversation|false
    {
        $conversations = $this->findBy(["context" => $contextId]);
        foreach ($conversations as $conversation) {
            if ($conversation->getContext()->equals($contextId)) {
                return $conversation;
            }
        }
        return false;
    }
}
