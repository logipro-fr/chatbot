<?php

namespace Chatbot\Application\Service\FindId;

use Chatbot\Application\Service\Exception\BadTypeNameException;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class FindId
{
    public function __construct(private ConversationRepositoryInterface $repo)
    {
    }

    private const CONVERSATIONS = "conversations";
    private const CONTEXTS = "contexts";

    public function find(string $IdType, string $id): ContextId
    {
        switch ($IdType) {
            case self::CONVERSATIONS:
                $conversation = $this->repo->findById(new ConversationId($id));
                return new ContextId($conversation->getContext());
            case self::CONTEXTS:
                return new ContextId($id);
            default:
                throw new BadTypeNameException("Please use 'conversations' or 'contexts");
        }
    }
}
