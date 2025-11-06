<?php

namespace Chatbot\Tests\Integration\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Symfony\Component\HttpClient\MockHttpClient;

class DeleteFileControllerFileApiTest extends FileApi
{
    public function __construct()
    {
        parent::__construct(new MockHttpClient());
    }

    public function delete(string $fileId): void
    {
    }
}
