<?php

namespace Chatbot\Infrastructure\LanguageModel;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;

class ParrotTranslate implements LanguageModelInterface
{
    private const TRANSLATION_PATTERN = "%s mais en %s";
    public function __construct(private string $lang)
    {
    }
    public function generateTextAnswer(Prompt $prompt): Answer
    {
        $answer = sprintf(self::TRANSLATION_PATTERN, $prompt->prompt, $this->lang);
        return new Answer($answer, 200);
    }
}
