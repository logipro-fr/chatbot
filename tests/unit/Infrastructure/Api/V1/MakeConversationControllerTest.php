<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\MakeConversationController;
use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class MakeConversationControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;
    use AssertResponseTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["context", "conversations", "conversations_pairs", "pairs"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testChatBotControllerExecute(): void
    {
        $factory = new ModelFactory();
        $controller = new MakeConversationController($factory, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversations/make",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Prompt" => "Chien",
                "lmName" => "ParrotTranslate",
                "context" => "base",
            ])
        );
        $response = $controller->makeConversation($request);
        /** @var string */
        $responseContent = $response->getContent();
        $this->assertJson($responseContent);
    }

    public function testControllerRouting(): void
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
        $data = $this->client->getResponse()->getContent();

        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
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
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
       /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        $this->assertTrue($responseContent["success"]);
        $this->assertEquals(200, $responseCode);
        $this->assertArrayHasKey("conversationId", $responseContent["data"]);
        $this->assertArrayHasKey("numberOfPairs", $responseContent["data"]);
        $this->assertArrayHasKey("botMessage", $responseContent["data"]);
    }


    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversations/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Chien",
                "lmName" => "",
                "context" => "base",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(ContextNotFoundException::class))->getShortName()
        );
    }
}
