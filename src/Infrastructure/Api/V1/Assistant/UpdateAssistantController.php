<?php

namespace Chatbot\Infrastructure\Api\V1\Assistant;

use Chatbot\Application\Service\UpdateAssistant\UpdateAssistant;
use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Infrastructure\Api\V1\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UpdateAssistantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UpdateAssistant $updateAssistantService
    ) {
    }

    #[Route('/api/v1/assistant/{ast_id}/update', 'updateAssistant', methods: ['PUT'])]
    public function updateAssistant(string $ast_id, Request $request): Response
    {
        try {
            $updateAssistantRequest = $this->buildUpdateAssistantRequest($ast_id, $request);

            $this->updateAssistantService->execute($updateAssistantRequest);
            $this->entityManager->flush();

            $response = $this->updateAssistantService->getResponse();
            return $this->writeSuccessfulResponse($response);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    private function buildUpdateAssistantRequest(string $assistantId, Request $request): UpdateAssistantRequest
    {
       // Lecture du body JSON
        $content = $request->getContent();
       /** @var array<string, mixed> $data */
        $data = json_decode($content, true) ?? [];

        if (empty($assistantId)) {
            throw new \InvalidArgumentException("L'ID de l'assistant est requis");
        }

       // --- NAME ---
        $nameValue = $data['name'] ?? null;
        if ($nameValue !== null && !is_string($nameValue)) {
            if (is_scalar($nameValue)) {
                 $nameValue = (string) $nameValue;
            } else {
                throw new \InvalidArgumentException("Le nom de l'assistant doit être une chaîne de caractères");
            }
        }

        $instructionValue = $data['instruction'] ?? null;
        if ($instructionValue !== null && !is_string($instructionValue)) {
            if (is_scalar($instructionValue)) {
                $instructionValue = (string) $instructionValue;
            } else {
                throw new \InvalidArgumentException("L'instruction doit être une chaîne de caractères");
            }
        }

        return new UpdateAssistantRequest(
            new AssistantId($assistantId),
            $nameValue,
            $instructionValue
        );
    }
}
