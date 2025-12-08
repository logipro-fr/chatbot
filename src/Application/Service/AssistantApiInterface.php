<?php

namespace Chatbot\Application\Service;

interface AssistantApiInterface
{
     /**
     * @param string[] $fileIds
     */
    public function createAssistant(string $name, string $instructions, array $fileIds = []): string;

    public function addMessageToThread(string $threadId, string $content, string $role = 'user'): string;

    public function createRun(string $threadId, string $assistantId): string;

    /**
     * @return array<string, string|int|bool>
     */
    public function getRunStatus(string $threadId, string $runId, int $baseDelay = 100000, int $attempt = 0): array;

    /**
     * @return array<array<string, string|int|bool|array<string, string|int|bool>>>
     */
    public function getMessages(string $threadId): array;

    /**
     * @return array<string, string|int|bool>
     */
    public function getAssistant(string $assistantId): array;


    /**
     * @return array<string, string|int|bool>
     */
    public function updateAssistant(string $assistantId, ?string $name = null, ?string $instructions = null): array;

    /**
     * @param array<string> $fileIds
     */
    public function createVectorStore(array $fileIds): string;

    /**
     * @return array<string, string|int|bool>
     */
    public function getVectorStore(string $vectorStoreId): array;

    /**
     * @return array<array<string, string|int|bool>>
     */
    public function getVectorStoreFiles(string $vectorStoreId): array;

    public function deleteVectorStore(string $vectorStoreId): void;

    public function deleteAssistant(string $assistantId): void;

    /**
     * @param array<string> $fileIds
     */
    public function updateAssistantFile(
        string $assistantId,
        ?array $fileIds = null,
        ?string $vectorStoreId = null
    ): void;

    /**
     * @param array<string> $fileIds
     */
    public function createVectorStoreFile(string $vectorStoreId, array $fileIds): void;

    public function deleteVectorStoreFile(string $vectorStoreId, string $fileId): void;
}
