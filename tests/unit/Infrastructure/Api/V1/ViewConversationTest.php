<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Application\Service\Exception\BadTypeNameException;
use Chatbot\Infrastructure\Api\V1\ViewConversationController;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ViewConversationTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;
    use AssertResponseTrait;

    private KernelBrowser $client;
    private string $id;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["context"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testViewContextControllerExecute(): void
    {
        $convrepo = new ConversationRepositoryInMemory();
        $controller = new ViewConversationController($convrepo, $this->getEntityManager());
        $request = Request::create(
            "GET",
            "/api/v1/conversations",
            [
                "Id" => "base",
            ],
            ['CONTENT_TYPE' => 'application/json'],
        );
        $response = $controller->viewConversation($request);
        /** @var string */
        $responseContent = $response->getContent();
        $this->assertJson($responseContent);
    }

    public function testControllerRouting(): void
    {

        $this->initializeConversationWithRouting();

        $this->client->request(
            "GET",
            "/api/v1/conversations",
            [
                "Id" => $this->id,
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
        $this->assertArrayHasKey("contextId", $responseContent["data"]);
    }


    public function testControllerException(): void
    {
        $this->client->request(
            "GET",
            "/api/v1/conversations",
            [
                "Id" => "Je n'existe pas",
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(ConversationNotFoundException::class))->getShortName()
        );
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

        $this->id = strval($responseContent['data']['conversationId']);
    }
}
