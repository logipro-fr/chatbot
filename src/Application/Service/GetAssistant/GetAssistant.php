<?php

namespace Chatbot\Application\Service\GetAssistant;

use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

class GetAssistant
{
    private ?GetAssistantResponse $response = null;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository,
        private ?AssistantApi $assistantApi = null
    ) {
    }

    public function execute(GetAssistantRequest $request): void
    {
        $assistant = $this->assistantRepository->findById($request->getAssistantId());

        if ($assistant === null) {
            throw new \InvalidArgumentException("Assistant non trouvé");
        }

        $externalData = null;
        if ($this->assistantApi !== null) {
            try {
                $externalData = $this->assistantApi->getAssistant($assistant->getExternalAssistantId());
            } catch (\Exception $e) {
                $externalData = null;
            }
        }

        $this->response = new GetAssistantResponse($assistant, $externalData);
    }

    public function getResponse(): ?GetAssistantResponse
    {
        return $this->response;
    }
}
