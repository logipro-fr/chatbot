<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\ChatBotMakeContext;
use Chatbot\Infrastructure\Api\V1\ChatBotMakeController;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotMakeContextTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["context"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testChatBotControllerExecute(): void
    {
        $repository = new ContextRepositoryInMemory();
        $controller = new ChatBotMakeContext($repository, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/context/make",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ContextMessage" => "You're helpfull assistant",
            ])
        );
        $response = $controller->makeContext($request);
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

                "ContextMessage" => "You're helpfull assistant",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"id":"cot_', $responseContent);
    }

    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/context/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "ContextMessage" => "",
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
