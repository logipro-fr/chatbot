<?php 

namespace Chatbot\Infrastructure\Persistence\Conversation;

use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;


/**
 * @extends EntityRepository<Map>
 */

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

    public function findById(ConversationId $conversationId): Conversation|false
    {
        $conversation = $this->getEntityManager()->find(Conversation::class, $conversationId);
        if ($conversation === null) {
            return false;
        }
        return $conversation;
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}