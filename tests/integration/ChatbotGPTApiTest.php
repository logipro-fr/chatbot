<?php

namespace Chatbot\Tests\integration;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\CurlHttpClient;

class ChatbotGPTApiTest extends TestCase
{
    public function setUp(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
    }

    public function testRequest(): void
    {
        $client = new CurlHttpClient();
        $conversation = new Conversation(new PairArray(), new ContextId("base"));
        $chatBotTest = new ChatbotGPTApi($client);
        $prompt = new Prompt("Comment t'appelles tu ?");
        $context = new Context(new ContextMessage("You're a sarcastic assistant named Marvin"));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);

        $this->assertEquals(true, is_string($response->message));
    }

    public function testTranslate(): void
    {
        $client = new CurlHttpClient();
        $conversation = new Conversation(new PairArray(), new ContextId("base"));
        $chatBotTest = new ChatbotGPTApi($client);
        $prompt = new Prompt("Comment t'appelles tu ?");
        $sentence = "You traduce the text your response start with 'le message en anglais est:'";
        $context = new Context(new ContextMessage($sentence));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals(true, is_string($response->message));
    }
}
