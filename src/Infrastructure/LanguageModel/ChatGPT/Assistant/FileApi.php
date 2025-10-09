<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\TooManyRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use Chatbot\Application\Service\Exception\MissingChatbotKeyApiException;

use function Safe\json_decode;
use function SafePHP\strval;

class FileApi
{
    private string $CHATBOT_KEY_API;

    public function __construct(
        private HttpClientInterface $client,
        ?string $apiKey = null
    ) {
        if ($apiKey == null) {
            if (!isset($_ENV["CHATBOT_KEY_API"])) {
                throw new MissingChatbotKeyApiException(
                    "Missing environment variable: CHATBOT_KEY_API is required to initialize FileApi."
                );
            }

            $apiKey = strval($_ENV["CHATBOT_KEY_API"]);
        }
        $this->CHATBOT_KEY_API = $apiKey;
    }

    public function upload(string $filePath, string $purpose = 'assistants'): string
    {
        if (!file_exists($filePath)) {
            throw new BadRequestException("File not found: " . $filePath);
        }

        $fileResource = fopen($filePath, 'r');
        if ($fileResource === false) {
            throw new BadRequestException("Cannot open file: " . $filePath);
        }

        $body = [
            'file' => $fileResource,
            'purpose' => $purpose
        ];

        $response = $this->client->request(
            'POST',
            'https://api.openai.com/v1/files',
            $this->paramsHeader($body, false)
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        /** @var object{id: string} $content */
        return $content->id;
    }

    public function delete(string $fileId): void
    {
        $response = $this->client->request(
            'DELETE',
            "https://api.openai.com/v1/files/{$fileId}",
            $this->paramsHeader([], false)
        );
        $this->handleResponse($response);
    }

    /**
     * @return array<array<string, string|int|bool>>
     */
    public function list(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.openai.com/v1/files',
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

    /**
     * @return array<string, string|int|bool>
     */
    public function get(string $fileId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/files/{$fileId}",
            $this->paramsHeader([], false)
        );
        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        /** @var array<string, string|int|bool> $content */
        return $content;
    }

    /**
     * @param array<string, string|int|bool|resource> $data
     * @return array<string, array<string, string>|array<string, string|int|bool|resource>>
     */
    private function paramsHeader(array $data, bool $isJson = true): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API
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
