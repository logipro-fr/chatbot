<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GPTModel implements LanguageModelInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private Context $context,
    ) {
    }

    public function generateTextAnswer(Prompt $prompt): Answer
    {

        $chatbot = new ChatbotGPTApi($this->httpClient);
        $response = $chatbot->request(new RequestGPT($prompt, $this->context));
        $message = new Answer($response->message, $response->statusCode);
        return $message;
    }
}
