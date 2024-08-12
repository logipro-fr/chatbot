<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\SwitchContextConversation\SwitchContextConversation;
use Chatbot\Application\Service\SwitchContextConversation\SwitchContextConversationRequest;
use Chatbot\Application\Service\SwitchContextConversation\SwitchContextConversationResponse;
use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationResponse;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\ConversationId;
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

class ChatBotSwitchController
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/conversations/SwitchContext', 'SwitchContextConversation', methods: ['POST'])]
    public function switchContextConversation(Request $request): Response
    {
        $request = $this->buildswitchContextConversationRequest($request);
        $conversation = new SwitchContextConversation($this->repository);
        try {
            $conversation->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $conversation->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function writeSuccessfulResponse(SwitchContextConversationResponse $conversationResponse): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => true,
                'errorCode' => "",
                'data' => [
                    'NewId' => $conversationResponse->contextId->__toString(),
                    'ConversationId' => $conversationResponse->conversation->getId(),

                ],
                    'message' => "",
            ],
            200
        );
    }

    private function writeUnSuccessFulResponse(Throwable $e): JsonResponse
    {
        $className = (new \ReflectionClass($e))->getShortName();
        return new JsonResponse(
            [
                'success' => false,
                'ErrorCode' => $className,
                'data' => '',
                'message' => $e->getMessage(),
            ],
        );
    }

    private function buildswitchContextConversationRequest(Request $request): SwitchContextConversationRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */

        $data = json_decode($content, true);
        /** @var ContextID */
        $contextId = new ContextId($data['ContextId']);
        /** @var ConversationId */
        $convId = new ConversationId($data['ConversationId']);



        return new SwitchContextConversationRequest($contextId, $convId,);
    }
}
