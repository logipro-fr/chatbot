<?php

namespace Chatbot\Tests\Unit\Domain\Model\Assistant;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class AssistantTest extends TestCase
{
    public function testShouldCreateAssistantWithBasicInformation(): void
    {
        $assistantId = new AssistantId();
        $name = "Assistant Documentation";
        $instructions = "Tu es un assistant spécialisé dans la documentation";
        $externalAssistantId = "asst_123456";

        $assistant = new Assistant($assistantId, $name, $instructions, $externalAssistantId);

        $this->assertEquals($assistantId, $assistant->getAssistantId());
        $this->assertEquals($name, $assistant->getName());
        $this->assertEquals($instructions, $assistant->getInstructions());
        $this->assertEquals($externalAssistantId, $assistant->getExternalAssistantId());
        $this->assertEmpty($assistant->getFileIds());
    }

    public function testShouldCreateAssistantWithFileIds(): void
    {
        $assistantId = new AssistantId();
        $name = "Assistant avec fichiers";
        $instructions = "Assistant avec fichiers attachés";
        $externalAssistantId = "asst_789";
        $fileIds = ["fil_123", "fil_456"];

        $assistant = new Assistant($assistantId, $name, $instructions, $externalAssistantId, $fileIds);

        $this->assertEquals($fileIds, $assistant->getFileIds());
    }

    public function testShouldAddFileIdToAssistant(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123"
        );

        $assistant->addFileId("fil_789");

        $this->assertContains("fil_789", $assistant->getFileIds());
    }

    public function testShouldNotAddDuplicateFileId(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123",
            ["fil_123"]
        );

        $assistant->addFileId("fil_123");

        $this->assertCount(1, $assistant->getFileIds());
        $this->assertContains("fil_123", $assistant->getFileIds());
    }

    public function testShouldRemoveFileIdFromAssistant(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123",
            ["fil_123", "fil_456"]
        );

        $assistant->removeFileId("fil_123");

        $this->assertNotContains("fil_123", $assistant->getFileIds());
        $this->assertContains("fil_456", $assistant->getFileIds());
    }

    public function testGetCreatedAt(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123"
        );

        $createdAt = $assistant->getCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $createdAt);
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $createdAt);
    }

    public function testShouldUpdateNameAndInstructions(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Old Name",
            "Old Instructions",
            "asst_123"
        );

        $assistant->setName("New Name");
        $assistant->setInstructions("New Instructions");

        $this->assertEquals("New Name", $assistant->getName());
        $this->assertEquals("New Instructions", $assistant->getInstructions());
    }

    public function testShouldUpdateOnlyName(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Old Name",
            "Old Instructions",
            "asst_123"
        );

        $assistant->setName("New Name");

        $this->assertEquals("New Name", $assistant->getName());
        $this->assertEquals("Old Instructions", $assistant->getInstructions());
    }

    public function testShouldUpdateOnlyInstructions(): void
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Old Name",
            "Old Instructions",
            "asst_123"
        );

        $assistant->setInstructions("New Instructions");

        $this->assertEquals("Old Name", $assistant->getName());
        $this->assertEquals("New Instructions", $assistant->getInstructions());
    }

    public function testAssistantAddandGetVectorId(): void 
    {
        $assistant = new Assistant(
            new AssistantId(),
            "Assistant Test",
            "Instructions",
            "asst_123"
        );

        $vectorId = "vs_456";
        $assistant->setVectorId($vectorId);

        $this->assertEquals($vectorId, $assistant->getVectorId());
    }
}
