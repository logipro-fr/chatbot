<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\TooManyRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use Chatbot\Application\Service\Exception\MissingChatbotKeyApiException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

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
            $envValue = $_ENV["CHATBOT_KEY_API"];
            if (!is_string($envValue)) {
                throw new MissingChatbotKeyApiException(
                    "Environment variable CHATBOT_KEY_API must be a string."
                );
            }
            $apiKey = $envValue;
        }
        $this->CHATBOT_KEY_API = $apiKey;
    }

    /**
     * @param array<string> $fileIds
     */
    public function createAssistant(string $name, string $instructions, array $fileIds = []): string
    {
        /** @var array<string, mixed> $requestData */
        $requestData = [
            'name' => $name,
            'instructions' => $instructions,
            'model' => 'gpt-4-turbo'
        ];

        if (!empty($fileIds)) {
            $this->validateFileIds($fileIds);

            $vectorStoreId = $this->createVectorStore($fileIds);

            $requestData['tools'] = [
                [
                    'type' => 'file_search'
                ]
            ];

            $requestData['tool_resources'] = [
                'file_search' => [
                    'vector_store_ids' => [$vectorStoreId]
                ]
            ];
        }

        try {
            $response = $this->client->request(
                'POST',
                'https://api.openai.com/v1/assistants',
                $this->paramsHeader($requestData)
            );

            $this->handleResponse($response);
            $content = json_decode($response->getContent());
            /** @var object{id: string} $content */
            return $content->id;
        } catch (ClientExceptionInterface $e) {
            $response = $e->getResponse();
            $content = $response->getContent(false);
            throw new BadRequestException("OpenAI API Error: " . $content);
        }
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
        /** @var object{id?: string} $content */
        if (!isset($content->id)) {
            throw new OtherException('Invalid response: missing thread ID');
        }
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
        /** @var object{id?: string} $content */
        if (!isset($content->id)) {
            throw new OtherException('Invalid response: missing message ID');
        }
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
        /** @var object{id?: string} $content */
        if (!isset($content->id)) {
            throw new OtherException('Invalid response: missing run ID');
        }
        return $content->id;
    }

    /**
     * @return array<string, string|int|bool>
     */
    public function getRunStatus(string $threadId, string $runId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/threads/{$threadId}/runs/{$runId}",
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        /** @var array<string, string|int|bool> $content */
        return $content;
    }

    /**
     * @return array<array<string, string|int|bool|array<string, string|int|bool>>>
     */
    public function getMessages(string $threadId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/threads/{$threadId}/messages",
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        /** @var array<string, mixed> $content */
        $data = $content['data'] ?? [];

        if (!is_array($data)) {
            return [];
        }

        /** @var array<array<string, string|int|bool|array<string, string|int|bool>>> $result */
        $result = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                /** @var array<string, string|int|bool|array<string, string|int|bool>> $item */
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return array<string, string|int|bool>
     */
    public function getAssistant(string $assistantId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/assistants/{$assistantId}",
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        /** @var array<string, string|int|bool> $content */
        return $content;
    }

    /**
     * @param array<string> $fileIds
     */
    public function createVectorStore(array $fileIds): string
    {
        $requestData = [
            'name' => 'Vector Store for Assistant',
            'file_ids' => $fileIds
        ];

        try {
            $response = $this->client->request(
                'POST',
                'https://api.openai.com/v1/vector_stores',
                $this->paramsHeader($requestData)
            );

            $this->handleResponse($response);
            $content = json_decode($response->getContent());
            /** @var object{id: string} $content */
            return $content->id;
        } catch (ClientExceptionInterface $e) {
            $response = $e->getResponse();
            $content = $response->getContent(false);
            throw new BadRequestException("OpenAI Vector Store API Error: " . $content);
        }
    }

    /**
     * @return array<string, string|int|bool>
     */
    public function getVectorStore(string $vectorStoreId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/vector_stores/{$vectorStoreId}",
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        /** @var array<string, string|int|bool> $content */
        return $content;
    }

    /**
     * @return array<array<string, string|int|bool>>
     */
    public function getVectorStoreFiles(string $vectorStoreId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files",
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        /** @var array<string, mixed> $content */
        $data = $content['data'] ?? [];

        if (!is_array($data)) {
            return [];
        }

        /** @var array<array<string, string|int|bool>> $result */
        $result = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                /** @var array<string, string|int|bool> $item */
                $result[] = $item;
            }
        }

        return $result;
    }

    public function deleteVectorStore(string $vectorStoreId): void
    {
        $response = $this->client->request(
            'DELETE',
            "https://api.openai.com/v1/vector_stores/{$vectorStoreId}",
            $this->paramsHeader([], false)
        );

        $this->handleResponse($response);
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


    /**
     * @param array<string, mixed> $data
     * @return array<string, array<string, string>|array<string, mixed>>
     */
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

    /**
     * @param array<string> $fileIds
     */
    private function validateFileIds(array $fileIds): void
    {
        foreach ($fileIds as $fileId) {
            if (empty($fileId)) {
                throw new BadRequestException("Invalid file ID: " . $fileId);
            }

            if (!preg_match('/^file-[a-zA-Z0-9]+$/', $fileId)) {
                throw new BadRequestException("Invalid file ID format: " . $fileId . ". Expected format: file-xxxxx");
            }
        }
    }

    private function handleResponse(\Symfony\Contracts\HttpClient\ResponseInterface $response): void
    {
        $code = $response->getStatusCode();

        if ($code === 200 || $code === 201) {
            return;
        } elseif ($code === 401) {
            $content = $response->getContent();
            throw new UnhautorizeKeyException("Unauthorized: Invalid or missing API key.");
        } elseif ($code === 400) {
            $content = $response->getContent();
            $decodedContent = json_decode($content, true);
            /** @var array<string, mixed> $decodedContent */
            $error = $decodedContent['error'] ?? [];
            /** @var array<string, mixed> $error */
            $message = $error['message'] ?? $content;
            if (!is_string($message)) {
                $errorMessage = 'Unknown error';
            } else {
                $errorMessage = $message;
            }
            throw new BadRequestException("Bad Request: " . $errorMessage);
        } elseif ($code === 429) {
            $content = $response->getContent();
            throw new TooManyRequestException("Too Many Requests: You have exceeded your request quota.");
        } else {
            $content = $response->getContent();
            throw new OtherException("Unexpected error: received HTTP status code " . $code . ". " . $content);
        }
    }
}
