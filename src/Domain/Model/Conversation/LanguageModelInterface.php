<?php

namespace Chatbot\Domain\Model\Conversation;

interface LanguageModelInterface
{
    public function generateTextAnswer(Prompt $prompt): Answer;
}
