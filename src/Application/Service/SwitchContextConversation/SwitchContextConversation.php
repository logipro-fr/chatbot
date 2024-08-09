<?php

namespace Chatbot\Application\Service\SwitchContextConversation;


use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use PhpParser\Node\Stmt\Switch_;

class SwitchContextConversation{
    private SwitchContextConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $conversationrepo)
    {
    }
    public function execute(SwitchContextConversationRequest $request): void
    {
        $conversation = $this->conversationrepo->findById($request->conversation);
        $conversation->SwitchContext($request->contextId);
        $this->response = new SwitchContextConversationResponse($conversation->getContext(), $conversation->getId());
    }

    public function getResponse(): SwitchContextConversationResponse
    {
        return $this->response;
    }
}