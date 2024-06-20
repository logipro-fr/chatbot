<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

use function Safe\json_decode;

class ChatBotMakeController
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private LanguageModelAbstractFactory $factory,
        private EntityManagerInterface $entityManager
    ) {
    }
    #[Route('api/v1/conversation/Make', 'makeConversation', methods: ['POST'])]
    public function makeConversation(Request $request): Response
    {
        $request = $this->buildMakeconversationRequest($request);

        $conversation = new MakeConversation($this->repository, $this->factory);

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


        /** @var string */
        $prompt = $data['Prompt'];
        /** @var string */
        $lmName = $data['lmName'];
        /** @var string */
        $context = $data['context'];

        return new MakeConversationRequest($prompt, $lmName, $context);
    }

    private function writeSuccessfulResponse(MakeConversationResponse $conversationResponse): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => true,
                'errorCode' => "",
                'data' => [
                    'id' => $conversationResponse->conversationId->__toString(),
                    'nbPair' => $conversationResponse->nbPair,
                    'lastPair' => $conversationResponse->pair,
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
}
