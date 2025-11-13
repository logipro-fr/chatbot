<?php

namespace Chatbot\Application\Service\ListConversations;

use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;

class ListConversations
{
    private ListConversationsResponse $response;

    public function __construct(
        private ThreadRepositoryInterface $threadRepository,
        private ConversationRepositoryInterface $conversationRepository
    ) {
    }

    public function execute(ListConversationsRequest $request): void
    {
        $threads = $this->threadRepository->findByAssistantId($request->assistantId);

        $conversations = [];
        foreach ($threads as $thread) {
            $conversation = $this->conversationRepository->findById($thread->getConversationId());

            $createdAt = $conversation->getCreatedAt();

            $conversations[] = new ListConversationItem(
                $conversation->getConversationId()->__toString(),
                $conversation->getTitle(),
                $conversation->getContext()->__toString(),
                $conversation->countPair(),
                $createdAt->format('Y-m-d H:i:s')
            );
        }

        usort($conversations, function (ListConversationItem $a, ListConversationItem $b) {
            return strtotime($b->createdAt) <=> strtotime($a->createdAt);
        });

        $this->response = new ListConversationsResponse($conversations);
    }

    public function getResponse(): ListConversationsResponse
    {
        return $this->response;
    }
}
