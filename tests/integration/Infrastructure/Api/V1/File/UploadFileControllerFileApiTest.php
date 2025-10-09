<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Symfony\Component\HttpClient\MockHttpClient;

class UploadFileControllerFileApiTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new MockHttpClient());
    }

    public function upload(string $filePath, string $purpose = 'assistants'): string
    {
        return 'fil-abc123';
    }
}
