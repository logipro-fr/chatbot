<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function execute(Request $request): Response
    {
        $request = $this->buildMakeconversationRequest($request);

        $conversation = new MakeConversation($this->repository, $this->factory);

        try {
            $conversation->execute($request);
            $this->entityManager->flush();
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'success' => false,
                    'statusCode' => '',
                    'data' => '',
                    'message' => "$e",
                ]
            );
        }
        $response = $conversation->getResponse();
        return new JsonResponse(
            [
                'success' => true,
                'errorCode' => "",
                'data' => [
                    'id' => $response->conversationId->__toString(),
                    'nbPair' => $response->nbPair,
                    'lastPair' => $response->pair,
                ],
                    'message' => "",
            ],
            200
        );
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
}
