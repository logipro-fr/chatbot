<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\TooManyRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use Chatbot\Application\Service\Exception\MissingChatbotKeyApiException;

use function Safe\json_decode;

class AssistantApi
{
    private string $CHATBOT_KEY_API;

    public function __construct(
        private HttpClientInterface $client,
        ?string $apiKey = null
    ) {
        if ($apiKey == null) {
            if (!isset($_ENV["CHATBOT_KEY_API"])) {
                throw new MissingChatbotKeyApiException(
                    "Missing environment variable: CHATBOT_KEY_API is required to initialize AssistantApi."
                );
            }
            /** @var string $apiKey */
            $apiKey = $_ENV["CHATBOT_KEY_API"];
        }
        $this->CHATBOT_KEY_API = $apiKey;
    }

    public function createAssistant(string $name, string $instructions, array $fileIds = []): string
    {
        $requestData = [
            'name' => $name,
            'instructions' => $instructions,
            'model' => 'gpt-4-turbo'
        ];

        if (!empty($fileIds)) {
            $requestData['file_ids'] = $fileIds;
        }

        $response = $this->client->request(
            'POST',
            'https://api.openai.com/v1/assistants',
            $this->paramsHeader($requestData)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function createThread(): string
    {
        $response = $this->client->request(
            'POST',
            'https://api.openai.com/v1/threads',
            $this->paramsHeader([])
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function addMessageToThread(string $threadId, string $content, string $role = 'user'): string
    {
        $requestData = [
            'role' => $role,
            'content' => $content
        ];

        $response = $this->client->request(
            'POST',
            "https://api.openai.com/v1/threads/{$threadId}/messages",
            $this->paramsHeader($requestData)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function createRun(string $threadId, string $assistantId): string
    {
        $requestData = [
            'assistant_id' => $assistantId
        ];

        $response = $this->client->request(
            'POST',
            "https://api.openai.com/v1/threads/{$threadId}/runs",
            $this->paramsHeader($requestData)
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
            $this->paramsHeader([], false)
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
            $this->paramsHeader([], false)
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
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
    }

    /**  @return  array<string, array<string, string>|array<string, mixed>> */
    private function paramsHeader(array $data, bool $isJson = true): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
            'OpenAI-Beta' => 'assistants=v2'
        ];

        if ($isJson) {
            $headers['Content-Type'] = 'application/json';
        }

        $params = [
            'headers' => $headers
        ];

        if (!empty($data)) {
            if ($isJson) {
                $params['json'] = $data;
            } else {
                $params['body'] = $data;
            }
        }

        return $params;
    }

    private function handleResponse($response): void
    {
        $code = $response->getStatusCode();

        if ($code === 200 || $code === 201) {
            return;
        } elseif ($code === 401) {
            $content = $response->getContent();
            throw new UnhautorizeKeyException("Unauthorized: Invalid or missing API key.");
        } elseif ($code === 400) {
            $content = $response->getContent();
            throw new BadRequestException("Bad Request: The request was invalid or cannot be processed.");
        } elseif ($code === 429) {
            $content = $response->getContent();
            throw new TooManyRequestException("Too Many Requests: You have exceeded your request quota.");
        } else {
            $content = $response->getContent();
            throw new OtherException("Unexpected error: received HTTP status code " . $code . ". " . $content);
        }
    }
}
