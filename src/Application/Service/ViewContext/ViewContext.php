<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;

class ViewContext
{
    private ViewContextResponse $response;


    public function __construct(
        private ContextRepositoryInterface $contextrepository,
    ) {
    }
    public function execute(ViewContextRequest $request): void
    {
        $context = $this->contextrepository->findById($request->contextId);

        $this->response = new ViewContextResponse($context->getContext());
    }

    public function getResponse(): ViewContextResponse
    {
        return $this->response;
    }
}
