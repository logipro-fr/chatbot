<?php

namespace Chatbot\Application\Service\ViewContext;

use Chatbot\Application\Service\FindId\FindId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;

class ViewContext
{
    private ViewContextResponse $response;


    public function __construct(
        private ContextRepositoryInterface $contextRepository,
        private ConversationRepositoryInterface $convRepository
    ) {
    }
    public function execute(ViewContextRequest $request): void
    {
        $finder = new FindId($this->convRepository);
        $id = $finder->find($request->type, $request->id);
        $context = $this->contextRepository->findById($id);

        $this->response = new ViewContextResponse($context->getContext()->getMessage());
    }

    public function getResponse(): ViewContextResponse
    {
        return $this->response;
    }
}
