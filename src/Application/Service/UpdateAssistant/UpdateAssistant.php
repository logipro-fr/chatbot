<?php

namespace Chatbot\Application\Service\UpdateAssistant;

use Chatbot\Application\Service\ChatGPT\AssistantApi;
use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantRequest;
use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantResponse;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;

class UpdateAssistant
{
    private UpdateAssistantResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository,
        private AssistantApi $assistantApi,
    ) {
    }

    public function execute(UpdateAssistantRequest $request): void
    {
        $assistant  = $this->assistantRepository->findById($request->assistantId);
        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant not found: " . $request->assistantId->getId());
        }

        // Mettre à jour l'assistant dans OpenAI
        $assistantExternalId = $assistant->getExternalAssistantId();
        $this->assistantApi->updateAssistant(
            $assistantExternalId,
            $request->newName,
            $request->newInstructions
        );

        // Mettre à jour les propriétés de l'assistant selon la requête
        if ($request->newName !== null) {
            $assistant->setName($request->newName);
        }

        if ($request->newInstructions !== null) {
            $assistant->setInstructions($request->newInstructions);
        }


        $this->assistantRepository->add($assistant);

        $this->response = new UpdateAssistantResponse(
            $assistant->getAssistantId(),
            $assistant->getName(),
            $assistant->getInstructions()
        );
    }

    public function getResponse(): UpdateAssistantResponse
    {
        return $this->response;
    }
}
