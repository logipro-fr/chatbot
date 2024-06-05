<?php

namespace Chatbot\Domain\Service\Ask ;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;

class Ask
{
    public function execute(Prompt $prompt, LanguageModelInterface $languageModel): Answer
    {
        $message = $languageModel->generateTextAnswer($prompt);
        return $message;
    }
}
