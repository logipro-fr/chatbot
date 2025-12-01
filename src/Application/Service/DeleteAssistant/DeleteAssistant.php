<?php

namespace Chatbot\Application\Service\DeleteAssistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Application\Service\ChatGPT\AssistantApi;

class DeleteAssistant
{
    private DeleteAssistantResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(DeleteAssistantRequest $request): void
    {
        $assistant = $this->assistantRepository->findById($request->assistantId);
        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant non trouvé: " . $request->assistantId->getId());
        }

        try {
            $this->assistantApi->deleteAssistant($assistant->getExternalAssistantId());
        } catch (\Exception $e) {
        }

        $this->assistantRepository->delete($request->assistantId);

        $this->response = new DeleteAssistantResponse(
            $assistant->getAssistantId(),
            $assistant->getExternalAssistantId()
        );
    }

    public function getResponse(): DeleteAssistantResponse
    {
        return $this->response;
    }
}
