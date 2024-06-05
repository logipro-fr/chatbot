<?php

namespace Chatbot\Application\Service\MakeConversation;

use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Service\Ask\Ask;

class MakeConversation
{
    private MakeConversationResponse $response;


    public function __construct(private ConversationRepositoryInterface $repository, private LanguageModelAbstractFactory $factory)
    {
    }
    public function execute(MakeConversationRequest $request): void
    {

        $lm = $this->factory->create($request->lmname, $request->context);
        $conversation = new Conversation(new PairArray());
        $message = (new Ask())->execute(new Prompt($request->prompt), $lm);
        $conversation->addPair(new Prompt($request->prompt), $message);
        $this->addToRepository($conversation);
        $this->response = new MakeConversationResponse(true, $message->getCodeStatus(), $conversation->getId(), $message->getMessage());
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
