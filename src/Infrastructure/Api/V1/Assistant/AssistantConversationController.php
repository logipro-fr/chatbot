<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\AssistantConversation\AssistantConversation;
use Chatbot\Application\Service\AssistantConversation\AssistantConversationRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Thread\ThreadRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;

class AssistantConversationController extends AbstractController
{
    public function __construct(
        private AssistantApi $assistantApi,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/conversation', 'assistantConversation', methods: ['POST'])]
    public function assistantConversation(Request $request): Response
    {
        try {
            $assistantConversationRequest = $this->buildAssistantConversationRequest($request);

            $service = new AssistantConversation(
                new ConversationRepositoryDoctrine($this->entityManager),
                new ContextRepositoryDoctrine($this->entityManager),
                new ThreadRepositoryDoctrine($this->entityManager),
                $this->assistantApi
            );
            $service->execute($assistantConversationRequest);
            $this->entityManager->flush();

            $response = $service->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildAssistantConversationRequest(Request $request): AssistantConversationRequest
    {
        $content = $request->getContent();
        /** @var array<string, string|int|bool> $data */
        $data = json_decode($content, true);

        $assistantId = (string) ($data['ast_id'] ?? $data['assistant_id'] ?? '');
        $message = (string) ($data['message'] ?? '');

        if (empty($message)) {
            throw new \InvalidArgumentException("Le message ne peut pas être vide");
        }

        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

        $assistantRepository = new AssistantRepositoryDoctrine($this->entityManager);
        $assistant = $assistantRepository->findById(new AssistantId($assistantId));

        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant non trouvé: " . $assistantId);
        }

        return new AssistantConversationRequest($assistant, $message);
    }
}
