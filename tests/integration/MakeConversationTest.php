<?php

namespace Chatbot\Tests\integration;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModel;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Chatbot\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\CurlHttpClient;

class MakeConversationTest extends BaseTestCase
{

    private string $API_KEY;

    public function setUp(): void
    {

        parent::setUp();

        // Initialiser la variable API_KEY à partir de l'environnement
        $this->API_KEY = getenv('API_KEY');

    }
    
    public function testMakeOneConversation(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $client = new CurlHttpClient();
        $factory = new ModelFactory($this->API_KEY);

        $request = new MakeConversationRequest("Bonjour, comment va tu ?", "GPTModel", "You're a helpfull assistant assistant ");
        $service = new MakeConversation($repository, $factory);
        $service->execute($request);

        $response = $service->getResponse();


        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $pair = $conversation->getPair(0);
        $responseMessage = $pair->getAnswer()->getMessage();
        //var_dump($responseMessage);
        $this->assertEquals(true, is_string($responseMessage));

        $request = new ContinueConversationRequest("Ca va super ! Quel temps fait il chez toi ?", new ConversationId($response->conversationId), "GPTModel", $client);
        $service = new ContinueConversation($repository, $factory);
        $service->execute($request);
        $pair = $conversation->getPair(1);
        $responseMessage = $pair->getAnswer()->getMessage();
        //var_dump($responseMessage);
        $this->assertEquals(true, is_string($responseMessage));
    }
}
