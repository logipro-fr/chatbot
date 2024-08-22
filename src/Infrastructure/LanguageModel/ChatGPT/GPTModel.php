<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GPTModel implements LanguageModelInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private Context $context,
        private Conversation $conversation,
    ) {
    }

    public function generateTextAnswer(Prompt $prompt): Answer
    {

        $chatbot = new ChatbotGPTApi($this->httpClient);
        $response = $chatbot->request(new RequestGPT($prompt, $this->context, $this->conversation));
        $message = new Answer($response->message, $response->statusCode);
        return $message;
    }
}
