<?php

namespace Chatbot\Application\Service\DeleteAssistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

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
            $this->assistantApi->deleteAssistant($assistant->getOpenAiAssistantId());
        } catch (\Exception $e) {
            // Ignore OpenAI deletion errors
        }

        $this->assistantRepository->delete($request->assistantId);

        $this->response = new DeleteAssistantResponse(
            $assistant->getAssistantId(),
            $assistant->getOpenAiAssistantId()
        );
    }

    public function getResponse(): DeleteAssistantResponse
    {
        return $this->response;
    }
}
