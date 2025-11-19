<?php

namespace Chatbot\Tests\integration\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ResponseGPT;
use Chatbot\Tests\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApiTest as ChatGPTChatbotGPTApiTest;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\CurlHttpClient;

class ChatbotGPTApiTest extends ChatGPTChatbotGPTApiTest
{
    public function setUp(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/.env.local');

        $this->client = new CurlHttpClient();
    }

    protected function assertResponseIsCorrect(string $messageResponse): void
    {
        $this->assertGreaterThan(3, strlen($messageResponse));
    }

    public function testRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $prompt = new Prompt("raconte moi une blague stp");
        $context = new Context(new ContextMessage("You're helpfull asistant"));
        $chatBotTest = new ChatbotGPTApi($this->client);
        $requestGPT = new RequestGPT($prompt, $context, $conversation);

        $response = $chatBotTest->request($requestGPT);

        $this->assertResponseIsCorrect($response->message);
    }

    public function testHeader(): void
    {
        $this->markTestSkipped('Test unitaire non applicable en intégration');
    }

    public function testBody(): void
    {
        $this->markTestSkipped('Test unitaire non applicable en intégration');
    }

    public function testRequestToRefacto(): void
    {
        $client = new CurlHttpClient();
        $conversation = new Conversation(new ContextId("base"));
        $chatBotTest = new ChatbotGPTApi($client);
        $prompt = new Prompt("Comment t'appelles tu ?");
        $context = new Context(new ContextMessage("You're a sarcastic assistant named Marvin"));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertStringContainsString("Marvin", $response->message);
    }

    public function testTranslate(): void
    {
        $client = new CurlHttpClient();
        $conversation = new Conversation(new ContextId("base"));
        $chatBotTest = new ChatbotGPTApi($client);
        $prompt = new Prompt("Comment t'appelles tu ?");
        $sentence = "You traduce the text your response start with 'le message en anglais est:'";
        $context = new Context(new ContextMessage($sentence));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertInstanceOf(ResponseGPT::class, $response);
    }
}
