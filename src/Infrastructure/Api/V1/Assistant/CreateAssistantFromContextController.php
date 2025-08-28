<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Infrastructure\Persistence\Assistant\AssistantRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\json_decode;

class CreateAssistantFromContextController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('api/v1/assistant/create-from-context', 'createAssistantFromContext', methods: ['POST'])]
    public function createAssistantFromContext(Request $request): Response
    {
        try {
            $createAssistantRequest = $this->buildCreateAssistantRequest($request);

            $service = new CreateAssistantFromContext(
                new AssistantRepositoryDoctrine($this->entityManager),
                new ContextRepositoryDoctrine($this->entityManager),
                new AssistantApi($this->client)
            );
            $service->execute($createAssistantRequest);
            $this->entityManager->flush();

            $response = $service->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildCreateAssistantRequest(Request $request): CreateAssistantFromContextRequest
    {
        $content = $request->getContent();
        /** @var array<string, mixed> $data */
        $data = json_decode($content, true);

        $contextId = new ContextId($data['context_id'] ?? '');
        $fileIds = $data['file_ids'] ?? [];

        if (empty($data['context_id'])) {
            throw new \InvalidArgumentException("L'ID du contexte est requis");
        }

        return new CreateAssistantFromContextRequest($contextId, $fileIds);
    }
}
