<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\ChatBotMakeController;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotMakeControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["conversations"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testChatBotControllerExecute(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory();
        $contextrepo = new ContextRepositoryInMemory();
        $controller = new ChatBotMakeController($repository, $contextrepo, $factory, $this->getEntityManager());
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
        $contextid = $responseContent['data']['id'];

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
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"id":"con_', $responseContent);
        $this->assertStringContainsString('"numberOfPairs":', $responseContent);
        $this->assertStringContainsString('"lastPair":', $responseContent);
        $this->assertStringContainsString('"Answer":"Chien', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
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
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString('"success":false', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"data":"', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
