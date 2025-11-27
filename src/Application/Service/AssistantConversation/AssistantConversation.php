<?php

namespace Chatbot\Application\Service\AssistantConversation;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Thread\Thread;
use Chatbot\Domain\Model\Thread\ThreadId;
use Chatbot\Domain\Model\Thread\ThreadRepositoryInterface;
use Chatbot\Application\Service\Exception\AssistantMessageNotFoundException;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\AssistantApi;

class AssistantConversation
{
    private AssistantConversationResponse $response;

    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
        private ContextRepositoryInterface $contextRepository,
        private ThreadRepositoryInterface $threadRepository,
        private AssistantApi $assistantApi
    ) {
    }

    public function execute(AssistantConversationRequest $request): void
    {
        $context = $this->getOrCreateContextFromAssistant($request->assistant);

        $conversation = new Conversation($context->getContextId());
        $this->conversationRepository->add($conversation);

        $threadId = $this->assistantApi->createThread();

        $thread = new Thread(
            new ThreadId(),
            $request->assistant->getAssistantId(),
            $threadId,
            $conversation->getConversationId()
        );
        $this->threadRepository->add($thread);

        $this->assistantApi->addMessageToThread($threadId, $request->message, 'user');

        $runId = $this->assistantApi->createRun($threadId, $request->assistant->getExternalAssistantId());

        $this->waitForRunCompletion($threadId, $runId);

        $messages = $this->assistantApi->getMessages($threadId);

        $lastAssistantMessage = $this->findLastAssistantMessage($messages);
        $cleanedMessage = $this->cleanMetadata($lastAssistantMessage);

        $conversation->addPair(
            new Prompt($request->message),
            new Answer($cleanedMessage, 200)
        );

        $this->response = new AssistantConversationResponse(
            $conversation->getConversationId(),
            $cleanedMessage
        );
    }

    private function getOrCreateContextFromAssistant(Assistant $assistant): Context
    {
        $instructions = $assistant->getInstructions();

        $existingContext = $this->contextRepository->findByMessage($instructions);

        if ($existingContext !== null) {
            return $existingContext;
        }

        $context = new Context(
            new ContextMessage($instructions)
        );
        $this->contextRepository->add($context);

        return $context;
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

        throw new AssistantMessageNotFoundException("Aucun message de l'assistant trouvé");
    }


    private function waitForRunCompletion(string $threadId, string $runId): void
    {
        $this->assistantApi->getRunStatus($threadId, $runId);
    }


    private function cleanMetadata(string $message): string
    {
        $cleaned = preg_replace('/【\d+:\d+†[^】]+】/', '', $message);
        $cleaned = preg_replace('/\d+:\d+†[^\s]+/', '', $cleaned ?? '');

        $cleaned = preg_replace('/\s+/', ' ', $cleaned ?? '');

        return trim($cleaned ?? '');
    }

    public function getResponse(): AssistantConversationResponse
    {
        return $this->response;
    }
}
