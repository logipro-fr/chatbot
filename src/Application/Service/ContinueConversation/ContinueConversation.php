<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Service\Ask\Ask;

class ContinueConversation
{
    private ContinueConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $repository,
        private LanguageModelAbstractFactory $factory
    ) {
    }

    public function execute(ContinueConversationRequest $request): void
    {

        /** @var Conversation */
        $conversation = $this->repository->findById($request->convId);
        $lm = $this->factory->create($request->lmName, $request->prompt->getUserResquest());
        $message = (new Ask())->execute(new Prompt($request->prompt->getUserResquest()), $lm);
        $conversation->addPair(new Prompt($request->prompt->getUserResquest()), $message);
        $pair = $conversation->getPair($conversation->getNbPair() - 1);
        $this->response = new ContinueConversationResponse(
            $conversation->getId(),
            $conversation->getNbPair(),
            $pair->getAnswer()->getMessage()
        );
    }

    public function getResponse(): ContinueConversationResponse
    {
        return $this->response;
    }
}
