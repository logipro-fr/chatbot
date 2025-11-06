<?php

namespace Chatbot\Application\Service\EditContext;

use Chatbot\Domain\Model\Context\ContextRepositoryInterface;

class EditContext
{
    private EditContextResponse $response;

    public function __construct(private ContextRepositoryInterface $repository)
    {
    }
    public function execute(EditContextRequest $request): void
    {
        $context = $this->repository->findById($request->id);
        $context->editMessage($request->message);
        $this->response = new EditContextResponse($context->getContextId(), $context->getContext());
    }

    public function getResponse(): EditContextResponse
    {
        return $this->response;
    }
}
