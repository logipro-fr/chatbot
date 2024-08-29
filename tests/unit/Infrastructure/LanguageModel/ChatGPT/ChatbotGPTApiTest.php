<?php

namespace Chatbot\Tests\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\Exception\BadInstanceException;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\ExcesRequestException;
use Chatbot\Application\Service\Exception\OtherException;
use Chatbot\Application\Service\Exception\UnhautorizeKeyException;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ChatbotGPTApi;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use Chatbot\Tests\RequestGPTFake;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;
use function Safe\json_encode;

class ChatbotGPTApiTest extends TestCase
{
    private string $content;
    private const CONTEXT = "You're helpfull asistant";


    public function setUp(): void
    {


        $this->content = <<<EOF
        {
            "model": "gpt-3.5-turbo",
            "messages": [
                {
                    "role": "system",
                    "content": "you're helpfull assistant"
                },
                {
                    "role": "user",
                    "content": "Hello !"
                }
            ]
        }
        EOF;
    }


    public function testRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $prompt = new Prompt("raconte moi une blague stp");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $chatBotTest = new ChatbotGPTApi($client);
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals("\n\nchats contre internet: souris gagnantes", $response->message);
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }

    public function testRequest2(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $client = $this->createMockHttpClient('responseGETbonjour.json', 200);
        $chatBotTest = new ChatbotGPTApi($client);
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertEquals(
            "\n\nBonjour ! Je vais bien merci ! comment puis-je vous aidez aujourd'hui",
            $response->message
        );
    }

    public function testHeader(): void
    {
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $response = (new ChatbotGPTApi($client))->paramsHeader($this->content);
        $this->assertEquals(
            ['Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . getenv("CHATBOT_API_KEY")
            ],
            $response["headers"]
        );
    }

    public function testBody(): void
    {
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $response = (new ChatbotGPTApi($client))->paramsHeader($this->content);
        $this->assertEquals($this->content, $response['body']);
    }

    public function testBadKey(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(UnhautorizeKeyException::class);
        $this->expectExceptionMessage("Bad Key");
        $client = $this->createMockHttpClient('responseGETblague.json', 401);
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        /** @var RequestGPT $requestGPT */
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testBadRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("Bad Request");
        $client = $this->createMockHttpClient('responseGETblague.json', 400);
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testExcesRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(ExcesRequestException::class);
        $this->expectExceptionMessage("Exceeded quota");
        $client = $this->createMockHttpClient('responseGETblague.json', 429);
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testOther(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(OtherException::class);
        $this->expectExceptionMessage("Other error");
        $client = $this->createMockHttpClient('responseGETblague.json', 404);
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testBadInstance(): void
    {
        $this->expectException(BadInstanceException::class);
        $this->expectExceptionMessage("Bad Instance");
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $requestGPT = new RequestGPTFake('bonjour comment va tu');
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testCreateContent(): void
    {
        $content = [
            "model" => "gpt-4-turbo",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "You're helpfull assistant"
                ],
                [
                    "role" => "user",
                    "content" => "Je suis le premier prompt"
                ],
                [
                    "role" => "assistant",
                    "content" => "Je suis la premiere reponse"
                ],
                [
                    "role" => "user",
                    "content" => "Je suis le deuxieme prompt"
                ],
                ]
            ];

        $conversation = new Conversation(new ContextId("base"));
        $conversation->addPair(
            new Prompt("Je suis le premier prompt"),
            new Answer("Je suis la premiere reponse", 200)
        );
        $client = $this->createMockHttpClient('responseGETblague.json', 200);
        $response = (new ChatbotGPTApi($client))->createContent(
            $conversation,
            "You're helpfull assistant",
            "Je suis le deuxieme prompt"
        );
        $this->assertEquals(
            json_encode($content),
            $response
        );
    }
}
