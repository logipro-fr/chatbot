<?php

namespace Chatbot\Application\Service\GetAssistant;

use Chatbot\Domain\Model\Assistant\Assistant;

class GetAssistantResponse
{
    /**
     * @param array<string, string|int|bool|array<string, string|int|bool>>|null $externalData
     */
    public function __construct(
        private Assistant $assistant,
        private ?array $externalData = null
    ) {
    }

    public function getAssistant(): Assistant
    {
        return $this->assistant;
    }

    /**
     * @return array<string, string|int|bool|array<string, string|int|bool>>|null
     */
    public function getExternalData(): ?array
    {
        return $this->externalData;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'assistantId' => $this->assistant->getAssistantId()->getId(),
            'name' => $this->assistant->getName(),
            'instructions' => $this->assistant->getInstructions(),
            'externalAssistantId' => $this->assistant->getExternalAssistantId(),
            'fileIds' => $this->assistant->getFileIds(),
            'createdAt' => $this->assistant->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        if ($this->externalData !== null) {
            $data['externalData'] = $this->externalData;
        }

        return $data;
    }
}
