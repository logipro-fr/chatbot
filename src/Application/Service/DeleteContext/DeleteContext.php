<?php

namespace Chatbot\Application\Service\DeleteContext;

use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class DeleteContext
{
    private DeleteContextResponse $response;

    public function __construct(
        private ContextRepositoryInterface $repository,
        private ConversationRepositoryInterface $convrepo
    ) {
    }
    public function execute(DeleteContextRequest $request): void
    {
        $conversation = $this->convrepo->findByContextId($request->id);
        if (false == $conversation) {
            $this->repository->removeContext($request->id);
            $this->response = new DeleteContextResponse("The conversation was deleted");
        } else {
            $this->response = new DeleteContextResponse(
                "the context can't be deleted because is associate to " . $conversation->getId() . "conversation"
            );
        }
    }

    public function getResponse(): DeleteContextResponse
    {
        return $this->response;
    }
}
