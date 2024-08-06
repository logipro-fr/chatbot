<?php

namespace Chatbot\Tests\Application\Service\MakeConversation;

use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use function Safe\file_get_contents;

class MakeConversationTest extends TestCase
{
    public function testSomeoneEngageAFirstSimpleConverdation(): void
    {
        // arrange / Given

        $repository = new ConversationRepositoryInMemory();
        $contextrepo = new ContextRepositoryInMemory;
        $request = new MakeConversationRequest(
            new Prompt("Bonjour"),
            "Parrot",
            new ContextId("base")
        );
        $factory = new ModelFactory();
        $service = new MakeConversation($repository, $factory, $contextrepo);
        //act / When
        $service->execute($request);

        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(MakeConversationResponse::class, $response);
    }

    public function testTwoConversation(): void
    {
        //arrange/ given
        $repository = new ConversationRepositoryInMemory();
        $contextrepo = new ContextRepositoryInMemory;
        $request = new MakeConversationRequest(
            new Prompt("Bonjour"),
            "Parrot",
            new ContextId("base")
        );
        $factory = new ModelFactory();
        $service = new MakeConversation($repository, $factory, $contextrepo);

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
        $contextrepo = new ContextRepositoryInMemory;
        $client = $this->createMockHttpClient("responseGETbonjour.json", 200);
        $request = new MakeConversationRequest(
            new Prompt("Bonjour"),
            "GPTModel",
            new ContextId("base")
        );

        $factory = new ModelFactory($client);
        $service = new MakeConversation($repository, $factory, $contextrepo);

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
        $contextrepo = new ContextRepositoryInMemory;
        $client = $this->createMockHttpClient("responseGETbonjour.json", 200);
        $request = new MakeConversationRequest(new Prompt("Bonjour"), "GPTModelTranslate", new ContextId("base"));
        $factory = new ModelFactory($client);
        $service = new MakeConversation($repository, $factory, $contextrepo);

        //act / When
        $service->execute($request);

        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(MakeConversationResponse::class, $response);
        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $this->assertGreaterThan(1, $conversation->getTotalToken());
    }
}
