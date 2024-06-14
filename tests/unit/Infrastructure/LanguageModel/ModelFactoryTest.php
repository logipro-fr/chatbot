<?php

namespace ChatBot\Tests\Application\Service\MakeConversation;

use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModel;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use Chatbot\Infrastructure\LanguageModel\Exception\BadLanguageModelName;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\LanguageModel\Parrot;
use Chatbot\Infrastructure\LanguageModel\ParrotTranslate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class ModelfactoryTest extends TestCase
{
    public function testCreateModelParrot(): void
    {
        $factory = new ModelFactory();
        $model = $factory->create("Parrot", "oui oui baguette");
        $this->assertInstanceOf(Parrot::class, $model);
    }

    public function testCreateModelGPT(): void
    {
        $client = $this->createMockHttpClient("responseGETbonjour.json", 200);
        $factory = new ModelFactory();

        $model = $factory->create("GPTModel", "oui oui baguette");
        $this->assertInstanceOf(GPTModel::class, $model);
    }


    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }

    public function testCreateModelGPTTranslate(): void
    {

        $client = $this->createMockHttpClient("responseGETbonjour.json", 200);
        $factory = new ModelFactory();
        $model = $factory->create("GPTModelTranslate", "anglais");
        $this->assertInstanceOf(GPTModelTranslate::class, $model);
    }

    public function testCreateModelParotranslate(): void
    {
        $client = $this->createMockHttpClient("responseGETbonjour.json", 200);
        $factory = new ModelFactory();
        $model = $factory->create("ParrotTranslate", "anglais");
        $this->assertInstanceOf(ParrotTranslate::class, $model);
    }

    public function testBadLmName(): void
    {

        $this->expectException(BadLanguageModelName::class);
        $client = $this->createMockHttpClient("responseGETbonjour.json", 200);
        $factory = new ModelFactory();
        $model = $factory->create("AModel", "anglais");
    }
}
