<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContext;
use Chatbot\Application\Service\CreateAssistantFromContext\CreateAssistantFromContextRequest;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
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
        private AssistantApi $assistantApi,
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
                $this->assistantApi
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
        /** @var array<string, string|int|bool|array<string>> $data */
        $data = json_decode($content, true);

        $contextIdValue = $data['context_id'] ?? '';
        if (!is_string($contextIdValue)) {
            throw new \InvalidArgumentException('Context ID must be a string');
        }
        $contextId = new ContextId($contextIdValue);

        $fileIdsArray = (array) ($data['file_ids'] ?? []);
        /** @var array<string> $fileIds */
        $fileIds = [];
        foreach ($fileIdsArray as $fileId) {
            if (is_string($fileId)) {
                $fileIds[] = $fileId;
            } elseif (is_scalar($fileId)) {
                $fileIds[] = (string) $fileId;
            }
        }

        if (empty($data['context_id'])) {
            throw new \InvalidArgumentException("L'ID du contexte est requis");
        }

        return new CreateAssistantFromContextRequest($contextId, $fileIds);
    }
}
