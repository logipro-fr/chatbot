<?php

namespace Chatbot\Application\Service\ContinueConversation;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Service\Ask\Ask;
use Chatbot\Infrastructure\Exception\noIdException;

class ContinueConversation
{
    private ContinueConversationResponse $response;

    public function __construct(private ConversationRepositoryInterface $repository, private LanguageModelAbstractFactory $factory)
    {
    }

    public function execute(ContinueConversationRequest $request): void
    {
        /** @var Conversation*/

        $conversation = $this->repository->findById($request->convId);
        if ($conversation == null) {
            throw new noIdException();
        }
        $lm = $this->factory->create($request->lmName, $request->prompt);
        $message = (new Ask())->execute(new Prompt($request->prompt), $lm);
        $conversation->addPair(new Prompt($request->prompt), $message);
        $this->response = new ContinueConversationResponse($conversation->getId(), $conversation->getPair($conversation->getNbPair()-1),$conversation->getNbPair());
    }

    public function getResponse(): ContinueConversationResponse
    {
        return $this->response;
    }
}
