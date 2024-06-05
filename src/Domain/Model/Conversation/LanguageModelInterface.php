<?php

namespace Chatbot\Domain\Model\Conversation;

interface LanguageModelInterface
{
    public function generateTextAnswer(Prompt $prompt): Answer;

   // public function generateTextTranslate(Prompt $prompt, string $lang): Answer;
}
