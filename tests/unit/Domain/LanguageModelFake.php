<?php

namespace Chatbot\Tests\Domain;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\LanguageModelInterface;
use Chatbot\Domain\Model\Conversation\Prompt;

class LanguageModelFake implements LanguageModelInterface
{
    /** @var array<Answer> */
    private array $answers = [];
    private int $nextAnswer = 0;
    public function generateTextAnswer(Prompt $prompt): Answer
    {
        $result = $this->answers[$this->nextAnswer];
        $this->nextAnswer++;
        return $result;
    }

    public function add(string $answer): void
    {
        $this->answers[] = new Answer($answer, 200);
    }

    public function generateTextTranslate(Prompt $prompt, string $lang): Answer
    {
        $answer = $prompt->prompt . " mais en " . $lang;
        return new Answer($answer, 200);
    }
}
