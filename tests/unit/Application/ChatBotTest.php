<?php

namespace Chatbot\Tests\Application;

use Chatbot\Application\Service\Request;
use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Api\ChatBot;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\RequestGPT;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\ResponseGPT;
use Chatbot\Tests\BaseTestCase;
use Chatbot\Tests\Domain\LanguageModelFake;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class ChatBotTest extends TestCase
{
 


    public function testConversation(): void
    {
        $request = new RequestGPT(new Prompt("allo"), new Context("tu es un assistant sympa"));
        $engine = new LanguageModelFake();
        $engine->add("\n\nBonjour ! Je vais bien merci ! comment puis-je vous aidez aujourd'hui");
        $conversation = new Conversation(new PairArray());
        $client = $this->createMockHttpClient('responseGETbonjour.json', 200);
        $chatBot = (new ChatBot($client))->conversation($request);

        $this->assertEquals(ResponseGPT::class, $chatBot::class);
        $this->assertEquals(
            "\n\nBonjour ! Je vais bien merci ! comment puis-je vous aidez aujourd'hui",
            $chatBot->message
        );
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }
}
