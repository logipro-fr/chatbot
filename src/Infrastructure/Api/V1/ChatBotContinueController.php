<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatBotContinueController
{
    public function __construct(
        private ConversationRepositoryInterface $repository,
        private LanguageModelAbstractFactory $factory
    ) {
    }
    #[Route('api/v1/conversation/Continue', 'continueConversation', methods: ['POST'])]
    public function execute(Request $request): Response
    {
        $request = $this->buildContinueconversationRequest($request);

        $conversation = new ContinueConversation($this->repository, $this->factory);

        try {
            $conversation->execute($request);
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'success' => false,
                    'statusCode' => '',
                    'data' => '',
                    'message' => '',
                ]
            );
        }
        $response = $conversation->getResponse();
        /** @var Conversation $conversation */
        $conversation = $this->repository->findById(new ConversationId($response->conversationId));
        $pair = $conversation->getPair(0);
        $responseMessage = $pair->getAnswer()->getMessage();
        $responseCode = $pair->getAnswer()->getCodeStatus();
        return new JsonResponse(
            [
                'success' => true,
                'statusCode' => $responseCode,
                'data' => "$response->conversationId",
                'message' => "$responseMessage",
            ]
        );
    }

    private function buildContinueconversationRequest(Request $request): ContinueConversationRequest
    {
        /** @var string */
        $prompt = $request->get('Prompt');
        /** @var string */
        $id = $request->get('convId');
        /** @var ConversationId */
        $convId = new ConversationId($id);
        /** @var string */
        $lmName = $request->get('lmName');


        return new ContinueConversationRequest($prompt, $convId, $lmName);
    }
}
