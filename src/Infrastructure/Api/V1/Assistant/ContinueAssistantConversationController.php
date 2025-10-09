<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversation;
use Chatbot\Application\Service\ContinueAssistantConversation\ContinueAssistantConversationRequest;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Thread\ThreadRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;

class ContinueAssistantConversationController extends AbstractController
{
    public function __construct(
        private AssistantApi $assistantApi,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/conversation/continue', 'continueAssistantConversation', methods: ['POST'])]
    public function continueAssistantConversation(Request $request): Response
    {
        try {
            $continueRequest = $this->buildContinueAssistantConversationRequest($request);

            $service = new ContinueAssistantConversation(
                new ConversationRepositoryDoctrine($this->entityManager),
                new ThreadRepositoryDoctrine($this->entityManager),
                new AssistantRepositoryDoctrine($this->entityManager),
                $this->assistantApi
            );
            $service->execute($continueRequest);
            $this->entityManager->flush();

            $response = $service->getResponse();
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
