<?php

namespace Chatbot\Application\Service\CreateAssistantFromContext;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

class CreateAssistantFromContext
{
    private CreateAssistantFromContextResponse $response;

    public function __construct(
        private AssistantRepositoryInterface $assistantRepository,
        private ContextRepositoryInterface $contextRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(CreateAssistantFromContextRequest $request): void
    {
        $context = $this->contextRepository->findById($request->contextId);
        if ($context === null) {
            throw new \InvalidArgumentException("Contexte non trouvé: " . $request->contextId->getId());
        }

        try {
            $openAiAssistantId = $this->assistantApi->createAssistant(
                "Assistant basé sur le contexte",
                $context->getContext()->getMessage(),
                $request->fileIds
            );

            $assistant = new Assistant(
                new AssistantId(),
                "Assistant basé sur le contexte",
                $context->getContext()->getMessage(),
                $openAiAssistantId,
                $request->fileIds
            );

            $this->assistantRepository->add($assistant);

            $this->response = new CreateAssistantFromContextResponse(
                $assistant->getAssistantId(),
                $openAiAssistantId
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getResponse(): CreateAssistantFromContextResponse
    {
        return $this->response;
    }
}
