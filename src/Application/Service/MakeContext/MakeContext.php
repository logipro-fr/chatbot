<?php

namespace Chatbot\Application\Service\MakeContext;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;

class MakeContext
{
    private MakeContextResponse $response;


    public function __construct(
        private ContextRepositoryInterface $repository,
    ) {
    }
    public function execute(MakeContextRequest $request): void
    {
        $context = new Context($request->message);
        $this->addToRepository($context);
        $this->response = new MakeContextResponse($context->getId());
    }
    private function addToRepository(Context $context): void
    {
        $this->repository->add($context);
    }

    public function getResponse(): MakeContextResponse
    {
        return $this->response;
    }
}
