<?php

namespace Chatbot\Infrastructure\LanguageModel;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;

class Parrot implements LanguageModelInterface
{
    public function generateTextAnswer(Prompt $prompt): Answer
    {
        $result = new Answer($prompt->prompt, 200);
        return $result;
    }
}
