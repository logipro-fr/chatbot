<?php

namespace Chatbot\Tests\Application\Service\UpdateAssistantFiles;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class UpdateAssistantFilesResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = ['file1', 'file2', 'file3'];

        $response = new UpdateAssistantFilesResponse($assistantId, $fileIds);

        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($fileIds, $response->fileIds);
    }

    public function testConstructorWithEmptyFileIds(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = [];

        $response = new UpdateAssistantFilesResponse($assistantId, $fileIds);

        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($fileIds, $response->fileIds);
    }
}
