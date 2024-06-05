<?php

namespace Chatbot\Infrastructure\LanguageModel;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;

class ParrotTranslate implements LanguageModelInterface
{
    public function __construct(private string $lang)
    {
    }
    public function generateTextAnswer(Prompt $prompt): Answer
    {
        $answer = $prompt->prompt . " mais en " . $this->lang;
        return new Answer($answer, 200);
    }
}
