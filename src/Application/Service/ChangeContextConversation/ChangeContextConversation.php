<?php

namespace Chatbot\Application\Service\ChangeContextConversation;


use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class ChangeContextConversation{
    private ChangeContextConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $conversationrepo)
    {
    }
    public function execute(ChangeContextConversationRequest $request): void
    {
        $conversation = $this->conversationrepo->findById($request->conversation);
        $conversation->ChangeContext($request->contextId);
        $this->response = new ChangeContextConversationResponse($conversation->getContext(), $conversation->getId());
    }

    public function getResponse(): ChangeContextConversationResponse
    {
        return $this->response;
    }
}