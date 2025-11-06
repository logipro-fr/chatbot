<?php

namespace Chatbot\Application\Service\ViewConversation;

use Chatbot\Application\Service\ViewConversation\ViewConversationRequest;
use Chatbot\Application\Service\ViewConversation\ViewConversationResponse;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Pair;

class ViewConversation
{
    private ViewConversationResponse $response;


    public function __construct(
        private ConversationRepositoryInterface $convRepository
    ) {
    }
    public function execute(ViewConversationRequest $request): void
    {
        $conversation = $this->convRepository->findById(new ConversationId($request->id));

        $this->response = new ViewConversationResponse($conversation->getContext(), $this->pairArray($conversation));
    }

    public function getResponse(): ViewConversationResponse
    {
        return $this->response;
    }

    /** @return array<int, Pair>  */
    public function pairArray(Conversation $conversation): array
    {
        $pairArray = [];
        $nbPair = $conversation->countPair();
        for ($i = 0; $i < $nbPair; $i++) {
            $pairArray[] = $conversation->getPair($i);
        }
        return $pairArray;
    }
}
