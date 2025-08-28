<?php

namespace Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\ExcesRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;

use function Safe\json_decode;

class FileApi
{
    private string $CHATBOT_KEY_API;

    public function __construct(
        private HttpClientInterface $client,
        ?string $apiKey = null
    ) {
        $this->CHATBOT_KEY_API = $apiKey ?? $_ENV["CHATBOT_KEY_API"];
    }

    public function upload(string $filePath, string $purpose = 'assistants'): string
    {
        if (!file_exists($filePath)) {
            throw new BadRequestException("Le fichier n'existe pas: " . $filePath);
        }

        $response = $this->client->request(
            'POST',
            'https://api.openai.com/v1/files',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                ],
                'body' => [
                    'file' => fopen($filePath, 'r'),
                    'purpose' => $purpose
                ]
            ]
        );

        $this->handleResponse($response);
        $content = json_decode($response->getContent());
        return $content->id;
    }

    public function delete(string $fileId): void
    {
        $response = $this->client->request(
            'DELETE',
            "https://api.openai.com/v1/files/{$fileId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                ]
            ]
        );
        $this->handleResponse($response);
    }

    public function list(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.openai.com/v1/files',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                ]
            ]
        );
        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        return $content['data'] ?? [];
    }

    public function get(string $fileId): array
    {
        $response = $this->client->request(
            'GET',
            "https://api.openai.com/v1/files/{$fileId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->CHATBOT_KEY_API,
                ]
            ]
        );
        $this->handleResponse($response);
        $content = json_decode($response->getContent(), true);
        return $content;
    }

    private function handleResponse($response): void
    {
        $code = $response->getStatusCode();
        if ($code === 200 || $code === 201) {
            return;
        } elseif ($code === 401) {
            throw new UnhautorizeKeyException("Bad Key");
        } elseif ($code === 400) {
            throw new BadRequestException("Bad Request");
        } elseif ($code === 429) {
            throw new ExcesRequestException("Exceeded quota");
        } else {
            throw new OtherException("Other error: " . $code);
        }
    }
}
