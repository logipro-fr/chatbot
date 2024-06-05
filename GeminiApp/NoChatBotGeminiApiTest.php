<?php

namespace Chatbot\Tests\unit;

use Behat\Testwork\Tester\Setup\Setup;
use PHPUnit\Framework\TestCase;
use Chatbot\ChatbotGeminiApi;
use Chatbot\ChatbotGPTApi;
use Chatbot\RequestGPT;
use Chatbot\ResponseGPT;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function PHPUnit\Framework\assertEquals;
use function Safe\file_get_contents;

class ChatbotGeminiApiTest extends TestCase
{
    //private string $apiKey;
    private string $content;
    private const PROJECT_ID = "Mon projet";
    private const CONTEXT = "You're helpfull asistant";

    public function setUp(): void
    {

        $this->content = <<<EOF
        {
            "contents": [
              {
                "role": "USER",
                "parts": { "text": "Hello!" }
              },
              {
                "role": "MODEL",
                "parts": { "text": "Argh! What brings ye to my ship?" }
              },
              {
                "role": "USER",
                "parts": { "text": "Wow! You are a real-life priate!" }
              }
            ],
            "safety_settings": {
              "category": "HARM_CATEGORY_SEXUALLY_EXPLICIT",
              "threshold": "BLOCK_LOW_AND_ABOVE"
            },
            "generation_config": {
              "temperature": 0.2,
              "topP": 0.8,
              "topK": 40,
              "maxOutputTokens": 200,
            }
          }
        EOF;
        //$this->apiKey =  'my api';
    }


    public function testRequest(): void
    {
        $client = $this->createMockHttpClient('responsePOSTGeminiBonjour.json');
        $chatBotTest = new ChatbotGeminiApi($client);
        $requestGPT = new RequestGPT('bonjour comment ca va', self::CONTEXT);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals("Bonjour ! Ça va bien, merci, et vous ?", $response->message);
    }

    private function createMockHttpClient(string $filename): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/ressources/' . $filename)),
        ];

        return new MockHttpClient($responses, 'https://us-central1-aiplatform.googleapis.com/v1/projects/');
    }

    public function testRequest2(): void
    {
        $client = $this->createMockHttpClient('responsePOSTGeminiBlague.json');
        $chatBotTest = new ChatbotGeminiApi($client);
        $requestGPT = new RequestGPT('bonjour comment va tu', self::CONTEXT);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals(
            "Pourquoi est-ce que les poissons nagent-ils dans l'eau salée ?" .
            " Parce que l'eau poivrée les ferait éternuer !",
            $response->message
        );
    }

    public function testHeader(): void
    {
        $client = $this->createMockHttpClient('responsePOSTGeminiBlague.json');
        $response = (new ChatbotGeminiApi($client))->paramsHeader($this->content);
        $this->assertEquals(
            ['Content-Type' => 'application/json',
            'Authorization' => 'Bearer '
            ],
            $response["headers"]
        );
    }

    public function testBody(): void
    {
        $client = $this->createMockHttpClient('responsePOSTGeminiBlague.json');
        $response = (new ChatbotGeminiApi($client))->paramsHeader($this->content);
        $this->assertEquals($this->content, $response['body']);
    }

    public function testUrl(): void
    {
        $client = $this->createMockHttpClient('responsePOSTGeminiBlague.json');
        $response = (new ChatbotGeminiApi($client))->url();
        $this->assertEquals('https://{REGION}-aiplatform.googleapis.com/v1/projects/' . self::PROJECT_ID .
        '/locations/{REGION}/publishers/google/models/gemini-1.0-pro:streamGenerateContent', $response);
    }
}
