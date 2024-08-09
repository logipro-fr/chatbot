<?php

namespace Chatbot\Application\Service\DeleteContext;

use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class DeleteContext{
    private DeleteContextResponse $response;

    public function __construct(
        private ContextRepositoryInterface $repository,
        private ConversationRepositoryInterface $convrepo)
    {
    }
    public function execute(DeleteContextRequest $request): void
    {
        
        if(false == $this->convrepo->findByContextId($request->id)){
            $this->repository->removeContext($request->id);
            $this->response = new DeleteContextResponse();
            
        }
        
        
    }

    public function getResponse(): DeleteContextResponse
    {
        return $this->response;
    }
}