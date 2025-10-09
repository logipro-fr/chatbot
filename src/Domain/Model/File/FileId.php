<?php

namespace Chatbot\Domain\Model\File;

class FileId
{
    public function __construct(private string $id = "")
    {
        if (empty($this->id)) {
            $this->id = uniqid("fil_");
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function equals(FileId $fileId): bool
    {
        return $this->id === $fileId->id;
    }
}
