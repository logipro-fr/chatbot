<?php

namespace Chatbot\Tests\Application\Service\UpdateAssistantFiles;

use Chatbot\Application\Service\UpdateAssistantFiles\UpdateAssistantFilesRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class UpdateAssistantFilesRequestTest extends TestCase
{
    public function testConstructor(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = ['file1', 'file2', 'file3'];
        $vectorId = 'vs_123456';

        $request = new UpdateAssistantFilesRequest($assistantId, $fileIds, $vectorId);

        $this->assertEquals($assistantId, $request->assistantId);
        $this->assertEquals($fileIds, $request->fileIds);
        $this->assertEquals($vectorId, $request->vectorId);
    }

    public function testConstructorWithEmptyFileIds(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $fileIds = [];
        $vectorId = 'vs_123456';

        $request = new UpdateAssistantFilesRequest($assistantId, $fileIds, $vectorId);

        $this->assertEquals($assistantId, $request->assistantId);
        $this->assertEquals($fileIds, $request->fileIds);
        $this->assertEquals($vectorId, $request->vectorId);
    }
}
