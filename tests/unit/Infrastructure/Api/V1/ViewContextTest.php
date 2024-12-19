<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\ViewContextController;
use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;
use function SafePHP\strval;

class ViewContextTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;
    use AssertResponseTrait;

    private KernelBrowser $client;
    private string $contextId;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["context", "conversations", "conversations_pairs", "pairs"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testViewContextControllerExecute(): void
    {

        $contextrepo = new ContextRepositoryInMemory();
        $controller = new ViewContextController($this->getEntityManager());
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

        $this->initializeContextWithRouting();

        $this->client->request(
            "GET",
            "/api/v1/contexts",
            [
                "Id" => $this->contextId,
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
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(ContextNotFoundException::class))->getShortName()
        );
    }

    private function initializeContextWithRouting(): void
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
        $this->contextId = strval($responseContent['data']['contextId']);
    }
}
