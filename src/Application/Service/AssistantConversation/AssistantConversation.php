<?php

namespace Chatbot\Application\Service\AssistantConversation;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

class AssistantConversation
{
    private AssistantConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private ContextRepositoryInterface $contextRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(AssistantConversationRequest $request): void
    {
        $context = $this->createContextFromAssistant($request->assistant);
        $this->contextRepository->add($context);

        $conversation = new Conversation($context->getContextId());
        $this->conversationRepository->add($conversation);

        $threadId = $this->assistantApi->createThread();

        $this->assistantApi->addMessageToThread($threadId, $request->message, 'user');

        $runId = $this->assistantApi->createRun($threadId, $request->assistant->getOpenAiAssistantId());

        $this->waitForRunCompletion($threadId, $runId);

        $messages = $this->assistantApi->getMessages($threadId);

        $lastAssistantMessage = $this->findLastAssistantMessage($messages);

        $conversation->addPair(
            new Prompt($request->message),
            new Answer($lastAssistantMessage, 200)
        );

        $this->response = new AssistantConversationResponse(
            $conversation->getConversationId(),
            $lastAssistantMessage
        );
    }

    private function createContextFromAssistant(Assistant $assistant): Context
    {
        return new Context(
            new ContextMessage($assistant->getInstructions())
        );
    }

    private function waitForRunCompletion(string $threadId, string $runId): void
    {
        $maxAttempts = 30; // 30 secondes max
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $runStatus = $this->assistantApi->getRunStatus($threadId, $runId);

            if ($runStatus['status'] === 'completed') {
                return;
            } elseif ($runStatus['status'] === 'failed') {
                throw new \RuntimeException("Le run a échoué: " . ($runStatus['last_error']['message'] ?? 'Erreur inconnue'));
            }

            sleep(1);
            $attempts++;
        }

        throw new \RuntimeException("Timeout: le run n'a pas été complété dans les temps");
    }

    private function findLastAssistantMessage(array $messages): string
    {
        foreach ($messages as $message) {
            if ($message['role'] === 'assistant') {
                return $message['content'][0]['text']['value'] ?? '';
            }
        }

        throw new \RuntimeException("Aucun message de l'assistant trouvé");
    }

    public function getResponse(): AssistantConversationResponse
    {
        return $this->response;
    }
}
