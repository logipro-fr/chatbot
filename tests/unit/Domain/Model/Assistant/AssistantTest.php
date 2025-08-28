<?php

namespace Chatbot\Tests\Unit\Domain\Model\Assistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class AssistantTest extends TestCase
{
    public function test_should_create_assistant_with_basic_information(): void
    {
        // Given
        $assistantId = new AssistantId();
        $name = "Assistant Documentation";
        $instructions = "Tu es un assistant spécialisé dans la documentation";
        $openAiAssistantId = "asst_123456";

        // When
        $assistant = new Assistant($assistantId, $name, $instructions, $openAiAssistantId);

        // Then
        $this->assertEquals($assistantId, $assistant->getAssistantId());
        $this->assertEquals($name, $assistant->getName());
        $this->assertEquals($instructions, $assistant->getInstructions());
        $this->assertEquals($openAiAssistantId, $assistant->getOpenAiAssistantId());
        $this->assertEmpty($assistant->getFileIds());
    }

    public function test_should_create_assistant_with_file_ids(): void
    {
        // Given
        $assistantId = new AssistantId();
        $name = "Assistant avec fichiers";
        $instructions = "Assistant avec fichiers attachés";
        $openAiAssistantId = "asst_789";
        $fileIds = ["file_123", "file_456"];

        // When
        $assistant = new Assistant($assistantId, $name, $instructions, $openAiAssistantId, $fileIds);

        // Then
        $this->assertEquals($fileIds, $assistant->getFileIds());
    }

    public function test_should_add_file_id_to_assistant(): void
    {
        // Given
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123"
        );

        // When
        $assistant->addFileId("file_789");

        // Then
        $this->assertContains("file_789", $assistant->getFileIds());
    }

    public function test_should_not_add_duplicate_file_id(): void
    {
        // Given
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123",
            ["file_123"]
        );

        // When
        $assistant->addFileId("file_123");

        // Then
        $this->assertCount(1, $assistant->getFileIds());
        $this->assertContains("file_123", $assistant->getFileIds());
    }

    public function test_should_remove_file_id_from_assistant(): void
    {
        // Given
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123",
            ["file_123", "file_456"]
        );

        // When
        $assistant->removeFileId("file_123");

        // Then
        $this->assertNotContains("file_123", $assistant->getFileIds());
        $this->assertContains("file_456", $assistant->getFileIds());
    }
}
