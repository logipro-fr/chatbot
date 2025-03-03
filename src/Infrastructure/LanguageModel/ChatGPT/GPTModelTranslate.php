<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\ContextFactory\ContextFactory;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GPTModelTranslate implements LanguageModelInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $lang,
        private Conversation $conversation,
    ) {
    }

    public function generateTextAnswer(Prompt $prompt): Answer
    {
        $chatbot = new ChatbotGPTApi($this->httpClient);
        $context = (new ContextFactory())->create($this->lang);
        $response = $chatbot->request(new RequestGPT($prompt, $context, $this->conversation));
        $message = new Answer($response->message, $response->statusCode);
        return $message;
    }
}
