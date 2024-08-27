<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Tests\Infrastructure\Api\V1\AssertResponseTrait;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\Api\V1\ContinueConversationController;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_decode;
use function Safe\json_encode;

class ContinueControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;
    use AssertResponseTrait;

    private KernelBrowser $client;
    /** @var string */
    private string $convId;
    private ModelFactory $factory;
    private ConversationRepositoryInMemory $inMemoryConversationRepository;
    private string $id;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["conversations_pairs","conversations","pairs"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testChatBotControllerExecute(): void
    {
        $this->initializeAConversation();
        $controller = new ContinueConversationController(
            $this->inMemoryConversationRepository,
            $this->factory,
            $this->getEntityManager()
        );
        $request = Request::create(
            "/api/v1/conversations/Continue",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Prompt" => "Bonjour",
                "convId" => $this->convId,
                "lmName" => "Parrot",
            ])
        );
        $response = $controller->continueConversation($request);
        /** @var string */
        $responseContent = $response->getContent();
        $this->assertJson($responseContent);
    }

    public function testControllerRouting(): void
    {
       $this->initializeConversationWithRouting();

        $this->client->request(
            "POST",
            "/api/v1/conversations/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Bonjour",
                "convId" => $this->id,
                "lmName" => "Parrot",
            ])
        );
        /** @var string */
        $content = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($content, true);

        $this->assertTrue($responseContent["success"]);
        $this->assertEquals(200, $responseCode);

        $data = $responseContent["data"];

        $this->assertEquals($this->id, $data["conversationId"]);
        $this->assertEquals(2, $data["numberOfPairs"]);
        $this->assertIsString($data["botMessage"]);
    }

    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversations/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Bonjour",
                "convId" => "con_5aez1gf4rz3251vf",
                "lmName" => "Parrot",
            ])
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
        $responseContent = json_decode($data, true);


        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(ConversationNotFoundException::class))->getShortName()
        );
    }
    
    private function initializeAConversation(): void
    {
        $this->factory = new ModelFactory();
        $this->inMemoryConversationRepository = new ConversationRepositoryInMemory();
        $inMemoryContextRepository = new ContextRepositoryInMemory();
        
        $request = new MakeConversationRequest(
            new Prompt("Bonjour"),
            "Parrot",
            new ContextId("base")
        );
        $service = new MakeConversation($this->inMemoryConversationRepository, $this->factory, $inMemoryContextRepository);
        $service->execute($request);
        $response = $service->getResponse();
        $this->convId = $response->conversationId;
    }

    private function initializeConversationWithRouting(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/context/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ContextMessage" => "je suis un context",
            ])
        );

        /** @var string */
        $content = $this->client->getResponse()->getContent();

        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($content, true);
        $contextid = $responseContent['data']['contextId'];

        $this->client->request(
            "POST",
            "/api/v1/conversations/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Chien",
                "lmName" => "ParrotTranslate",
                "context" => $contextid,
            ])
        );
        /** @var string */
        $content = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($content, true);

        $this->id = $responseContent['data']['conversationId'];
    }
}
