<?php

namespace Chatbot\Tests\Application\Service\MakeConversation;

use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Chatbot\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class MakeConversationTest extends BaseTestCase
{

    private string $API_KEY;

    public function setUp(): void
    {

        parent::setUp();

        // Initialiser la variable API_KEY à partir de l'environnement
        $this->API_KEY = getenv('API_KEY');
    }

    public function testSomeoneEngageAFirstSimpleConverdation(): void
    {
        // arrange / Given

        $repository = new ConversationRepositoryInMemory();
        $request = new MakeConversationRequest("Bonjour", "Parrot", "You're helpfull assistant");
        $factory = new ModelFactory($this->API_KEY);
        $service = new MakeConversation($repository, $factory);
        //act / When
        $service->execute($request);

        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(MakeConversationResponse::class, $response);


     //   $this->assertInstanceOf(Response::class,$response->execute(new Request($prompt)));
    }

    public function testTwoConversation(): void
    {
        //arrange/ given
        $repository = new ConversationRepositoryInMemory();
        $request = new MakeConversationRequest("Bonjour", "Parrot", "You're helpfull assistant");
        $factory = new ModelFactory($this->API_KEY);
        $service = new MakeConversation($repository, $factory);

        //act / When
        $service->execute($request);

        $response = $service->getResponse();
        $service->execute($request);
        $responseOtherConversation = $service->getResponse();

        //assert /Then
        $this->assertNotEquals($response->conversationId, $responseOtherConversation->conversationId);


        $conversation = $repository->findById(new ConversationId($response->conversationId->__toString()));
        $this->assertGreaterThan(1, $conversation->getTotalToken());
    }


    public function testWithChatGPTModel(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $request = new MakeConversationRequest("Bonjour", "GPTModel", "Your're helpfull assistant", $this->createMockHttpClient("responseGETbonjour.json", 200));
        
        $factory = new ModelFactory($this->API_KEY);
        $service = new MakeConversation($repository, $factory);

        //act / When
        $service->execute($request);

        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(MakeConversationResponse::class, $response);
        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $this->assertGreaterThan(1, $conversation->getTotalToken());
    }


    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/../../../ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }

    public function testWithChatGPTModelTranslate(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $request = new MakeConversationRequest("Bonjour", "GPTModelTranslate", "english", $this->createMockHttpClient("responseGETbonjour.json", 200));
        $factory = new ModelFactory($this->API_KEY);
        $service = new MakeConversation($repository, $factory);

        //act / When
        $service->execute($request);

        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(MakeConversationResponse::class, $response);
        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $this->assertGreaterThan(1, $conversation->getTotalToken());
    }
}
