<?php

namespace Chatbot\Tests\integration;

use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use Chatbot\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;

class ChatbotGPTApiTest extends BaseTestCase
{

    private string $API_KEY;

    public function setUp(): void
    {

        parent::setUp();

        // Initialiser la variable API_KEY à partir de l'environnement
        $this->API_KEY = getenv('API_KEY');

    }
    
    public function testRequest(): void
    {
        $client = new CurlHttpClient();
        $chatBotTest = new ChatbotGPTApi($client,$this->API_KEY);
        $prompt = new Prompt("Comment t'appelles tu ?");
        $context = new Context("You're a sarcastic assistant named Marvin");
        $requestGPT = new RequestGPT($prompt, $context);
        $response = $chatBotTest->request($requestGPT);
       // var_dump($response->message);
        $this->assertEquals(true, is_string($response->message));
    }

    public function testTranslate(): void
    {
        $client = new CurlHttpClient();
        $chatBotTest = new ChatbotGPTApi($client, $this->API_KEY);
        $prompt = new Prompt("Comment t'appelles tu ?");
        $sentence = "You're a english teacher and you traduce the text your response start with 'le message en anglais est:'";
        $context = new Context($sentence);
        $requestGPT = new RequestGPT($prompt, $context);
        $response = $chatBotTest->request($requestGPT);
        //var_dump($response->message);
        $this->assertEquals(true, is_string($response->message));
    }
}
