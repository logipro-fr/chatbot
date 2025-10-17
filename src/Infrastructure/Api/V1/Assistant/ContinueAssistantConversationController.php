<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversation;
use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationRequest;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\json_decode;

class ContinueAssistantConversationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContinueAssistantConversation $continueAssistantConversationService
    ) {
    }

    #[Route('api/v1/assistant/conversation/continue', 'continueAssistantConversation', methods: ['POST'])]
    public function continueAssistantConversation(Request $request): Response
    {
        try {
            $continueRequest = $this->buildContinueAssistantConversationRequest($request);

            $this->continueAssistantConversationService->execute($continueRequest);
            $this->entityManager->flush();

            $response = $this->continueAssistantConversationService->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildContinueAssistantConversationRequest(Request $request): ContinueAssistantConversationRequest
    {
        $content = $request->getContent();
        /** @var array<string, string|int|bool> $data */
        $data = json_decode($content, true);

        $conversationId = (string) ($data['conversation_id'] ?? '');
        $message = (string) ($data['message'] ?? '');

        if (empty($message)) {
            throw new \InvalidArgumentException("Le message ne peut pas être vide");
        }

        if (empty($conversationId)) {
            throw new \InvalidArgumentException("L'ID de la conversation est requis");
        }

        return new ContinueAssistantConversationRequest(
            new ConversationId($conversationId),
            $message
        );
    }
}
