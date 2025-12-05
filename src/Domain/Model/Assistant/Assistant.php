<?php

namespace Chatbot\Domain\Model\Assistant;

use Chatbot\Domain\Event\AssistantCreated;
use Chatbot\Domain\EventFacade\EventFacade;
use DateTimeImmutable;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class Assistant
{
    /** @var array<string> */
    private array $fileIds;

    /**
     * @param array<string> $fileIds
     */
    public function __construct(
        private AssistantId $assistantId,
        private string $name,
        private string $instructions,
        private string $externalAssistantId,
        array $fileIds = [],
        private readonly DateTimeImmutable $createdAt = new SafeDateTimeImmutable()
    ) {
        /** @var array<string> $fileIds */
        $this->fileIds = $fileIds;
        (new EventFacade())->dispatch(new AssistantCreated($this->assistantId, $this->name));
    }

    public function getAssistantId(): AssistantId
    {
        return $this->assistantId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function getExternalAssistantId(): string
    {
        return $this->externalAssistantId;
    }

    /**
     * @return array<string>
     */
    public function getFileIds(): array
    {
        return $this->fileIds;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function addFileId(string $fileId): void
    {
        if (!in_array($fileId, $this->fileIds)) {
            $this->fileIds[] = $fileId;
        }
    }

    public function removeFileId(string $fileId): void
    {
        $this->fileIds = array_filter($this->fileIds, fn($id) => $id !== $fileId);
    }
}
