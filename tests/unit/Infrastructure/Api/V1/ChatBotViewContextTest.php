<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Application\Service\Exception\BadTypeNameException;
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
    use AssertResponseTrait;

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
            "GET",
            "/api/v1/contexts",
            [
                "Id" => "base",
                "IdType" => "contexts",
            ],
            ['CONTENT_TYPE' => 'application/json'],
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
        $contextId = $responseContent['data']['contextId'];

        $this->client->request(
            "GET",
            "/api/v1/contexts",
            [
                "Id" => $contextId,
                "IdType" => "contexts",
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);

        $this->assertTrue($responseContent["success"]);
        $this->assertEquals(200, $responseCode);
        $this->assertArrayHasKey("contextMessage", $responseContent["data"]);
    }


    public function testControllerException(): void
    {
        $this->client->request(
            "GET",
            "/api/v1/contexts",
            [
                "Id" => "Je n'existe pas",
                "IdType" => "context",
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(BadTypeNameException::class))->getShortName()
        );
    }
}
