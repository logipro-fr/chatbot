<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Symfony\Component\HttpClient\MockHttpClient;

class ReadFileControllerFileApiTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new MockHttpClient());
    }

    public function list(): array
    {
        return [
            [
                'id' => 'fil-abc123',
                'filename' => 'document1.pdf',
                'purpose' => 'assistants',
                'bytes' => 1024
            ],
            [
                'id' => 'file-def456',
                'filename' => 'document2.pdf',
                'purpose' => 'assistants',
                'bytes' => 2048
            ]
        ];
    }

    public function get(string $fileId): array
    {
        return [
            'id' => $fileId,
            'filename' => 'document.pdf',
            'purpose' => 'assistants',
            'bytes' => 1024
        ];
    }
}
