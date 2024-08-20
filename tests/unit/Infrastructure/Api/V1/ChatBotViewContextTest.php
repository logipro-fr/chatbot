<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\ChatBotViewContextController;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotViewContextTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["context"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testViewContextControllerExecute(): void
    {

        $contextrepo = new ContextRepositoryInMemory();
        $convrepo = new ConversationRepositoryInMemory();
        $controller = new ChatBotViewContextController($contextrepo, $convrepo, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversation/View",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => "base",
                "IdType" => "contexts",
            ])
        );
        $response = $controller->viewContext($request);
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
            "/api/v1/context/View",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => $contextid,
                "IdType" => "contexts",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"context":"je suis un context', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }


    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/context/View",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
              "Id" => "je n'existe pas",
              "IdType" => "context",
            ])
        );
        
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
        $this->assertResponseIsSuccessful();
        $responseContent = json_decode($data, true);


        $this->assertFalse($responseContent["success"]);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('BadTypeNameException', $responseContent["ErrorCode"]);
        $this->assertStringContainsString("Please use 'conversations' or 'contexts", $responseContent["message"]);
    }
}
