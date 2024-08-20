<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationResponse;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
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

class ChatBotContinueController
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private LanguageModelAbstractFactory $factory,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/conversations/Continue', 'continueConversation', methods: ['POST'])]
    public function continueConversation(Request $request): Response
    {
        $request = $this->buildContinueconversationRequest($request);
        $conversation = new ContinueConversation($this->repository, $this->factory);
        try {
            $conversation->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return $this->writeUnSuccessFulResponse($e);
        }
        $response = $conversation->getResponse();
        return $this->writeSuccessfulResponse($response);
    }

    private function writeSuccessfulResponse(ContinueConversationResponse $conversationResponse): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => true,
                'errorCode' => "",
                'data' => [
                    'id' => $conversationResponse->conversationId->__toString(),
                    'numberOfPairs' => $conversationResponse->numberOfPairs,
                    'lastPair' => $conversationResponse->pair,
                    'Answer' => $conversationResponse->botMessage,
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

    private function buildContinueconversationRequest(Request $request): ContinueConversationRequest
    {

        $content = $request->getContent();
        /** @var array<string> $data */

        $data = json_decode($content, true);
        /** @var Prompt */
        $prompt = new Prompt($data['Prompt']);
        /** @var string */
        $id = $data["convId"];
        /** @var ConversationId */
        $convId = new ConversationId($id);
        /** @var string */
        $lmName = $data['lmName'];


        return new ContinueConversationRequest($prompt, $convId, $lmName);
    }
}
