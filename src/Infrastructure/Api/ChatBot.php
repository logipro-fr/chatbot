<?php

namespace Chatbot\Infrastructure\Api;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ResponseGPT;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatBot
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }
    public function conversation(RequestGPT $request): ResponseGPT
    {
        $response = (new ChatbotGPTApi($this->client))->request($request);

        return $response;
    }
}
