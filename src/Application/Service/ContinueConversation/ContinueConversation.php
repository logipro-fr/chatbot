<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Service\Ask\Ask;

class ContinueConversation
{
    private ContinueConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $repository,
        private ContextRepositoryInterface $repositoryContext,
        private LanguageModelAbstractFactory $factory
    ) {
    }

    public function execute(ContinueConversationRequest $request): void
    {

        /** @var Conversation */
        $conversation = $this->repository->findById($request->convId);
        $context = $this->repositoryContext->findById($conversation->getContext());

        $lm = $this->factory->create($request->lmName, $context->getContext()->getMessage(), $conversation);
        $message = (new Ask())->execute(new Prompt($request->prompt->getUserResquest()), $lm);

        $conversation->addPair(new Prompt($request->prompt->getUserResquest()), $message);
        $pair = $conversation->getLastPair();
        $this->response = new ContinueConversationResponse(
            $conversation->getConversationId(),
            $conversation->countPair(),
            $pair->getAnswer()->getMessage()
        );
    }

    public function getResponse(): ContinueConversationResponse
    {
        return $this->response;
    }
}
