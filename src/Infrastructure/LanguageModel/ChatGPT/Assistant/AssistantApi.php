<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\ExcesRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;

use function Safe\json_decode;

class AssistantApi
{
    private string $CHATBOT_KEY_API;

    public function __construct(
        private HttpClientInterface $client,
        ?string $apiKey = null
    ) {
        $this->CHATBOT_KEY_API = $apiKey ?? $_ENV["CHATBOT_KEY_API"];
    }

    public function createAssistant(string $name, string $instructions, array $fileIds = []): string
    {
        $requestData = [
            'name' => $name,
            'instructions' => $instructions,
            'model' => 'gpt-4-turbo'
        ];

        try {
            $response = $this->client->request(
                'POST',
                'https://api.openai.com/v1/assistants',
                [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => $requestData
                ]
            );

            $this->handleResponse($response);
            $content = json_decode($response->getContent());
            return $content->id;
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $response = $e->getResponse();
            $content = $response->getContent();
            throw new BadRequestException("OpenAI API Error: " . $content);
        }
    }

    public function createThread(): string
    {
        $response = $this->client->request(
            'POST',
            'https://api.openai.com/v1/threads',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function addMessageToThread(string $threadId, string $content, string $role = 'user'): string
    {
        $response = $this->client->request(
            'POST',
            "https://api.openai.com/v1/threads/{$threadId}/messages",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'role' => $role,
                    'content' => $content
                ]
            ]
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function createRun(string $threadId, string $assistantId): string
    {
        $response = $this->client->request(
            'POST',
            "https://api.openai.com/v1/threads/{$threadId}/runs",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'assistant_id' => $assistantId
                ]
            ]
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function getRunStatus(string $threadId, string $runId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        return $content;
    }

    public function getMessages(string $threadId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/threads/{$threadId}/messages",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        return $content['data'] ?? [];
    }

    public function deleteAssistant(string $assistantId): void
    {
        $response = $this->client->request(
            'DELETE',
            "https://api.openai.com/v1/assistants/{$assistantId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                    'Content-Type' => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]
        );

        $this->handleResponse($response);
    }

    private function handleResponse($response): void
    {
        $code = $response->getStatusCode();

        if ($code === 200 || $code === 201) {
            return;
        } elseif ($code === 401) {
            $content = $response->getContent();
            throw new UnhautorizeKeyException("Bad Key: " . $content);
        } elseif ($code === 400) {
            $content = $response->getContent();
            throw new BadRequestException("Bad Request: " . $content);
        } elseif ($code === 429) {
            $content = $response->getContent();
            throw new ExcesRequestException("Exceeded quota: " . $content);
        } else {
            $content = $response->getContent();
            throw new OtherException("Other error " . $code . ": " . $content);
        }
    }
}
