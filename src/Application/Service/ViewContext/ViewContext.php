<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Application\Service\FindId\FindId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class ViewContext
{
    private ViewContextResponse $response;


    public function __construct(
        private ContextRepositoryInterface $contextrepository,
        private ConversationRepositoryInterface $convrepository
    ) {
    }
    public function execute(ViewContextRequest $request): void
    {
        $finder = new FindId($this->convrepository);
        $id = $finder->find($request->type, $request->id);
        $context = $this->contextrepository->findById($id);

        $this->response = new ViewContextResponse($context->getContext());
    }

    public function getResponse(): ViewContextResponse
    {
        return $this->response;
    }
}
