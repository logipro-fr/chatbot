<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\ChatBotEditContextController;
use Chatbot\Infrastructure\Exception\NoIdException;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotEditContextControllerTest extends WebTestCase
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

    public function testEditContextControllerExecute(): void
    {

        $contextrepo = new ContextRepositoryInMemory();
        $controller = new ChatBotEditContextController($contextrepo, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversation/Edit",
            "PUT",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => "base",
                "NewMessage" => "context",
            ])
        );
        $response = $controller->editContext($request);
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
            "PUT",
            "/api/v1/context/Edit",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => $contextid,
                "NewMessage" => "new context",
            ])
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);

        $this->assertTrue($responseContent["success"]);
        $this->assertEquals(200, $responseCode);


        $this->assertArrayHasKey("contextId", $responseContent["data"]);
        $this->assertArrayHasKey("contextMessage", $responseContent["data"]);
    }

    public function testControllerException(): void
    {
        $this->client->request(
            "PUT",
            "/api/v1/context/Edit",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => "Je n'existe pas",
                "NewMessage" => "new context",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(NoIdException::class))->getShortName()
        );
    }
}
