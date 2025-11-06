<?php

namespace Chatbot\Tests\Infrastructure\languageModel\ChatGPT;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\Parrot;
use Chatbot\Infrastructure\LanguageModel\ParrotTranslate;
use PHPUnit\Framework\TestCase;

class ParrotTest extends TestCase
{
    public function testParrotAnswer(): void
    {
        $parrot = new Parrot();
        $answer = new Answer("Bonjour", 200);
        $this->assertEquals($answer, $parrot->generateTextAnswer(new Prompt("Bonjour")));
    }

    public function testParrotTranslate(): void
    {
        $parrot = new ParrotTranslate("anglais");
        $answer = new Answer("Bonjour mais en anglais", 200);
        $this->assertEquals($answer, $parrot->generateTextAnswer(new Prompt("Bonjour")));
    }
}
