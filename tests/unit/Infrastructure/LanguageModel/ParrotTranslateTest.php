<?php

namespace Chatbot\Tests\Infrastructure\LanguageModel;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ParrotTranslate;
use PHPUnit\Framework\TestCase;

class ParrotTranslateTest extends TestCase
{
    public function testGenerateTextAnswerWithFrenchLanguage(): void
    {
        $parrotTranslate = new ParrotTranslate('français');
        $prompt = new Prompt('Hello, how are you?');

        $result = $parrotTranslate->generateTextAnswer($prompt);

        $this->assertInstanceOf(Answer::class, $result);
        $this->assertEquals('Hello, how are you? mais en français', $result->getMessage());
        $this->assertEquals(200, $result->getCodeStatus());
    }

    public function testGenerateTextAnswerWithSpanishLanguage(): void
    {
        $parrotTranslate = new ParrotTranslate('espagnol');
        $prompt = new Prompt('Good morning');

        $result = $parrotTranslate->generateTextAnswer($prompt);

        $this->assertInstanceOf(Answer::class, $result);
        $this->assertEquals('Good morning mais en espagnol', $result->getMessage());
        $this->assertEquals(200, $result->getCodeStatus());
    }

    public function testGenerateTextAnswerWithEmptyPrompt(): void
    {
        $parrotTranslate = new ParrotTranslate('allemand');
        $prompt = new Prompt('');

        $result = $parrotTranslate->generateTextAnswer($prompt);

        $this->assertInstanceOf(Answer::class, $result);
        $this->assertEquals(' mais en allemand', $result->getMessage());
        $this->assertEquals(200, $result->getCodeStatus());
    }
}
