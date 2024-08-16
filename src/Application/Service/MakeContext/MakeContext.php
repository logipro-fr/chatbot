<?php

namespace Chatbot\Application\Service\MakeContext;

use Chatbot\Application\Service\Exception\EmptyStringException;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\throwException;

class MakeContext
{
    private MakeContextResponse $response;


    public function __construct(
        private ContextRepositoryInterface $repository,
    ) {
    }
    public function execute(MakeContextRequest $request): void
    {
        if (empty($request->message->getMessage())) {
            throw new EmptyStringException();
        }
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
