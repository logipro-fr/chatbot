<?php

namespace Chatbot\Application\Service\ContinueAssistantConversation;

use Chatbot\Domain\Model\Assistant\AssistantRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

class ContinueAssistantConversation
{
    private ContinueAssistantConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private ThreadRepositoryInterface $threadRepository,
        private AssistantRepositoryInterface $assistantRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(ContinueAssistantConversationRequest $request): void
    {
        $conversation = $this->conversationRepository->findById($request->conversationId);

        $thread = $this->threadRepository->findByConversationId($request->conversationId);
        if ($thread === null) {
            throw new \RuntimeException("Aucun thread trouvé pour cette conversation");
        }

        $this->assistantApi->addMessageToThread($thread->getExternalThreadId(), $request->message, 'user');

        $assistant = $this->assistantRepository->findById($thread->getAssistantId());
        if ($assistant === null) {
            throw new \RuntimeException("Assistant non trouvé");
        }
        $runId = $this->assistantApi->createRun($thread->getExternalThreadId(), $assistant->getExternalAssistantId());

        $this->waitForRunCompletion($thread->getExternalThreadId(), $runId);

        $messages = $this->assistantApi->getMessages($thread->getExternalThreadId());

        $lastAssistantMessage = $this->findLastAssistantMessage($messages);
        $cleanedMessage = $this->cleanMetadata($lastAssistantMessage);

        $conversation->addPair(
            new Prompt($request->message),
            new Answer($cleanedMessage, 200)
        );

        $this->response = new ContinueAssistantConversationResponse(
            $conversation->getConversationId()->__toString(),
            $cleanedMessage
        );
    }

    private function waitForRunCompletion(string $threadId, string $runId): void
    {
        $maxAttempts = 30;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $runStatus = $this->assistantApi->getRunStatus($threadId, $runId);

            if ($runStatus['status'] === 'completed') {
                return;
            } elseif ($runStatus['status'] === 'failed') {
                /** @var array<string, mixed> $lastError */
                $lastError = $runStatus['last_error'] ?? [];
                $message = $lastError['message'] ?? 'Erreur inconnue';
                $errorMessage = is_string($message) ? $message : 'Erreur inconnue';
                throw new \RuntimeException("Le run a échoué: " . $errorMessage);
            }

            sleep(1);
            $attempts++;
        }

        throw new \RuntimeException("Timeout: le run n'a pas été complété dans les temps");
    }

    /**
     * @param array<array<string, mixed>> $messages
     */
    private function findLastAssistantMessage(array $messages): string
    {
        foreach ($messages as $message) {
            if ($message['role'] === 'assistant') {
                $contentArray = $message['content'] ?? [];
                if (is_array($contentArray) && !empty($contentArray)) {
                    /** @var array<string, mixed> $content */
                    $content = $contentArray[0] ?? [];
                    /** @var array<string, mixed> $text */
                    $text = $content['text'] ?? [];
                    $value = $text['value'] ?? '';
                    return is_string($value) ? $value : '';
                }
            }
        }

        throw new \RuntimeException("Aucun message de l'assistant trouvé");
    }

    private function cleanMetadata(string $message): string
    {
        $cleaned = preg_replace('/【\d+:\d+†[^】]+】/', '', $message);
        $cleaned = preg_replace('/\d+:\d+†[^\s]+/', '', $cleaned ?? '');

        $cleaned = preg_replace('/\s+/', ' ', $cleaned ?? '');

        return trim($cleaned ?? '');
    }

    public function getResponse(): ContinueAssistantConversationResponse
    {
        return $this->response;
    }
}
