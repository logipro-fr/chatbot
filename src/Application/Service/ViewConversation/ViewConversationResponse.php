<?php

namespace Chatbot\Application\Service\ViewConversation;

use Chatbot\Domain\Model\Conversation\Pair;

class ViewConversationResponse
{
    /**
     * @param array<int,Pair>$pairs
     */
    public function __construct(
        public readonly string $contextId,
        public readonly string $title,
        public readonly array $pairs
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $pairsArray = [];
        foreach ($this->pairs as $pair) {
            $pairsArray[] = [
                'prompt' => $pair->getPrompt()->getUserResquest(),
                'answer' => $pair->getAnswer()->getMessage(),
                'codeStatus' => $pair->getAnswer()->getCodeStatus(),
            ];
        }

        return [
            'contextId' => $this->contextId,
            'title' => $this->title,
            'pairs' => $pairsArray,
        ];
    }
}
