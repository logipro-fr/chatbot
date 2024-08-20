<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function Safe\json_decode;

class ChatBotMakeController extends AbstractController
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private ContextRepositoryInterface $contextRepository,
        private LanguageModelAbstractFactory $factory,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/conversations/Make', 'makeConversation', methods: ['POST'])]
    public function makeConversation(Request $request): Response
    {
        $request = $this->buildMakeconversationRequest($request);

        $conversation = new MakeConversation($this->repository, $this->factory, $this->contextRepository);

        try {
            $conversation->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $conversation->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function buildMakeconversationRequest(Request $request): MakeConversationRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */

        $data = json_decode($content, true);


        /** @var Prompt */
        $prompt = new Prompt($data["Prompt"]);
        /** @var string */
        $lmName = $data['lmName'];
        /** @var ContextId */
        $context = new ContextId($data['context']);

        return new MakeConversationRequest($prompt, $lmName, $context);
    }
}
