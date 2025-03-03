<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Service\Ask\Ask;

class MakeConversation
{
    private MakeConversationResponse $response;


    public function __construct(
        private ConversationRepositoryInterface $repository,
        private LanguageModelAbstractFactory $factory,
        private ContextRepositoryInterface $contextrepository,
    ) {
    }
    public function execute(MakeConversationRequest $request): void
    {
        $context = $this->contextrepository->findById($request->contextId);
        $conversation = new Conversation($request->contextId);
        $lm = $this->factory->create($request->lmname, $context->getContext()->getMessage(), $conversation);

        $message = (new Ask())->execute($request->prompt, $lm);
        $conversation->addPair($request->prompt, $message);
        $this->addToRepository($conversation);
        $pair = $conversation->getPair($conversation->countPair() - 1);
        $this->response = new MakeConversationResponse(
            $conversation->getConversationId(),
            $conversation->countPair(),
            $pair->getAnswer()->getMessage()
        );
    }
    private function addToRepository(Conversation $conversation): void
    {
        $this->repository->add($conversation);
    }

    public function getResponse(): MakeConversationResponse
    {
        return $this->response;
    }
}
