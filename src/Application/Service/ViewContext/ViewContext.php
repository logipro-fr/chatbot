<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Application\Service\FindId\FindId;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class ViewContext
{
    private ViewContextResponse $response;


    public function __construct(
        private ContextRepositoryInterface $contextRepository,
    ) {
    }
    public function execute(ViewContextRequest $request): void
    {
        $context = $this->contextRepository->findById(new ContextId($request->id));

        $this->response = new ViewContextResponse($context->getContext()->getMessage());
    }

    public function getResponse(): ViewContextResponse
    {
        return $this->response;
    }
}
