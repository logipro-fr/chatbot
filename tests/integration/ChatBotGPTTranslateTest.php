<?php

namespace Chatbot\Tests\integration;

use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeTranslate\MakeTranslate;
use Chatbot\Application\Service\MakeTranslate\MakeTranslateRequest;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Chatbot\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\CurlHttpClient;

class ChatBotGPTTranslateTest extends BaseTestCase
{
    private string $API_KEY;

    public function setUp(): void
    {
        parent::setUp();
        $apiKey = getenv('API_KEY');
        if ($apiKey === false) {
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
        }
    }

    public function testTranslate(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $client = new CurlHttpClient();
        $factory = new ModelFactory($this->API_KEY);
        $request = new MakeConversationRequest("Bonjour, comment ça va?", "GPTModelTranslate", "englis", $client);
        $service = new MakeConversation($repository, $factory);
        $service->execute($request);

        $response = $service->getResponse();


        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $pair = $conversation->getPair(0);
        $answer = $pair->getAnswer()->getMessage();
        $this->assertEquals("Hello, how are you?", $answer) ;
    }
}
