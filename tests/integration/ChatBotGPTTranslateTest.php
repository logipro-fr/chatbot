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
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\CurlHttpClient;

class ChatBotGPTTranslateTest extends TestCase
{
    public function setUp(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
    }



    public function testTranslate(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory();
        $request = new MakeConversationRequest("Bonjour, comment ça va?", "GPTModelTranslate", "englis");
        $service = new MakeConversation($repository, $factory);
        $service->execute($request);

        $response = $service->getResponse();


        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $pair = $conversation->getPair(0);
        $answer = $pair->getAnswer()->getMessage();
        $this->assertEquals("Hello, how are you?", $answer) ;
    }
}
