<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Symfony\Component\HttpClient\MockHttpClient;

class ReadFileControllerFileApiErrorTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new MockHttpClient());
    }

    public function list(): array
    {
        throw new \Exception('API Error');
    }

    public function get(string $fileId): array
    {
        throw new \Exception('API Error');
    }
}
