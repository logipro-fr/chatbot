<?php

namespace Chatbot\Tests\Infrastructure\LanguageModel\ChatGPT;

use Chatbot\Application\Service\Exception\BadInstanceException;
use Chatbot\Application\Service\Exception\BadRequestException;
use Chatbot\Application\Service\Exception\TooManyRequestException;
use Chatbot\Application\Service\Exception\MissingChatbotKeyApiException;
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
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\file_get_contents;
use function Safe\json_encode;

class ChatbotGPTApiTest extends TestCase
{
    private string $content;
    private const CONTEXT = "You're helpfull asistant";

    private ?string $savedChatbotApiKeyEnv = null;

    protected HttpClientInterface $client;

    public function setUp(): void
    {
        $this->saveChatbotApiKeyEnv();

        $_ENV['CHATBOT_KEY_API'] = 'test-api-key';

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

        $this->client = $this->createMockHttpClientSeveralPossibleResponses();
    }

    private function saveChatbotApiKeyEnv(): void
    {
        $this->savedChatbotApiKeyEnv = null;
        if (isset($_ENV['CHATBOT_KEY_API'])) {
            $envValue = $_ENV['CHATBOT_KEY_API'];
            if (is_string($envValue)) {
                $this->savedChatbotApiKeyEnv = $envValue;
            }
        }
    }

    protected function tearDown(): void
    {
        if ($this->savedChatbotApiKeyEnv !== null) {
            $_ENV['CHATBOT_KEY_API'] = $this->savedChatbotApiKeyEnv;
        } else {
            unset($_ENV['CHATBOT_KEY_API']);
        }
        $this->savedChatbotApiKeyEnv = null;
    }

    public function testRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $prompt = new Prompt("raconte moi une blague stp");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $chatBotTest = new ChatbotGPTApi($this->client);
        $requestGPT = new RequestGPT($prompt, $context, $conversation);

        $response = $chatBotTest->request($requestGPT);

        $this->assertEquals("\n\nchats contre internet: souris gagnantes", $response->message);
    }

    private function createMockHttpClientSeveralPossibleResponses(): MockHttpClient
    {
        $responses = [
            'marvin' => new MockResponse(
                file_get_contents(__DIR__ . '/../../../ressources/responseGETMarvin.json'),
                ['http_code' => 200]
            ),
            'blague' => new MockResponse(
                file_get_contents(__DIR__ . '/../../../ressources/responseGETblague.json'),
                ['http_code' => 200]
            ),
            'bonjour' => new MockResponse(
                file_get_contents(__DIR__ . '/../../../ressources/responseGETbonjour.json'),
                ['http_code' => 200]
            ),
        ];

        $chooseResponse = function (string $method, string $url, array $options = []) use ($responses) {
            $body = $options['body'] ?? '';

            foreach ($responses as $keyword => $response) {
                if (is_string($body) && str_contains($body, $keyword)) {
                    return $response;
                }
            }

            return reset($responses);
        };

        return new MockHttpClient(
            $chooseResponse,
            'https://api.openai.com/v1/chat/completion'
        );
    }



    public function testRequest2(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $chatBotTest = new ChatbotGPTApi($this->client);
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $response = $chatBotTest->request($requestGPT);
        $this->assertResponseIsCorrect($response->message);
    }

    protected function assertResponseIsCorrect(string $messageResponse): void
    {
        $this->assertEquals(
            "\n\nBonjour ! Je vais bien merci ! comment puis-je vous aidez aujourd'hui",
            $messageResponse
        );
    }

    public function testHeader(): void
    {
        $response = (new ChatbotGPTApi($this->client))->paramsHeader($this->content);
        $this->assertEquals(
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer test-api-key'
            ],
            $response["headers"]
        );
    }

    public function testBody(): void
    {
        $response = (new ChatbotGPTApi($this->client))->paramsHeader($this->content);
        $this->assertEquals($this->content, $response['body']);
    }

    public function testBadKey(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(UnhautorizeKeyException::class);
        $this->expectExceptionMessage("Unauthorized: Invalid or missing API key.");
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        /** @var RequestGPT $requestGPT */
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $client = $this->createMockHttpClientForStatus(401);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    private function createMockHttpClientForStatus(int $statusCode): MockHttpClient
    {
        $response = new MockResponse('', ['http_code' => $statusCode]);
        return new MockHttpClient($response, 'https://api.openai.com/v1/chat/completions');
    }

    public function testBadRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage("Bad Request: The request was invalid or cannot be processed.");
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $client = $this->createMockHttpClientForStatus(400);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testTooManyRequest(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(TooManyRequestException::class);
        $this->expectExceptionMessage("Too Many Requests: You have exceeded your request quota.");
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $client = $this->createMockHttpClientForStatus(429);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testOther(): void
    {
        $conversation = new Conversation(new ContextId("base"));
        $this->expectException(OtherException::class);
        $this->expectExceptionMessage("Unexpected error: received HTTP status code");
        $prompt = new Prompt("bonjour comment vas tu");
        $context = new Context(new ContextMessage(self::CONTEXT));
        $requestGPT = new RequestGPT($prompt, $context, $conversation);
        $client = $this->createMockHttpClientForStatus(404);
        (new ChatbotGPTApi($client))->request($requestGPT);
    }

    public function testBadInstance(): void
    {
        $this->expectException(BadInstanceException::class);
        $this->expectExceptionMessage("Invalid request instance: expected RequestGPT.");
        $requestGPT = new RequestGPTFake('bonjour comment va tu');
        $client = $this->createMockHttpClientForStatus(200);
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
        $response = (new ChatbotGPTApi($this->client))->createContent(
            $conversation,
            "You're helpfull assistant",
            "Je suis le deuxieme prompt"
        );
        $this->assertEquals(
            json_encode($content),
            $response
        );
    }

    public function testMissingChatbotKeyApiException(): void
    {
        $this->expectException(MissingChatbotKeyApiException::class);
        $this->expectExceptionMessage(
            "Missing environment variable: CHATBOT_KEY_API is required to initialize ChatbotGPTApi."
        );

        $savedKey = $_ENV['CHATBOT_KEY_API'] ?? null;

        unset($_ENV['CHATBOT_KEY_API']);

        try {
            new ChatbotGPTApi($this->client);
        } finally {
            if ($savedKey !== null) {
                $_ENV['CHATBOT_KEY_API'] = $savedKey;
            }
        }
    }

    public function testMissingChatbotKeyApiExceptionWhenNotString(): void
    {
        $this->expectException(MissingChatbotKeyApiException::class);
        $this->expectExceptionMessage(
            "Environment variable CHATBOT_KEY_API must be a string."
        );

        $savedKey = $_ENV['CHATBOT_KEY_API'] ?? null;

        $_ENV['CHATBOT_KEY_API'] = 123;

        try {
            new ChatbotGPTApi($this->client);
        } finally {
            if ($savedKey !== null) {
                $_ENV['CHATBOT_KEY_API'] = $savedKey;
            } else {
                unset($_ENV['CHATBOT_KEY_API']);
            }
        }
    }
}
