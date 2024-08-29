<?php

namespace Chatbot\Tests\integration\Infrastructure;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class ViewConversationControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private string $contextId;
    private string $conversationId;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
        $this->clearTables(["conversations_pairs", "pairs", "conversations"]);
        $this->client = self::createClient(["debug" => false]);

        $this->client->request(
            "POST",
            "/api/v1/context/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "ContextMessage" => "You're helpfull asistant",
                ]
            )
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        /** @var string */
        $id = $responseContent['data']['contextId'];
        $this->contextId = $id;

        $this->client->request(
            "POST",
            "/api/v1/conversations/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "Prompt" => "Chien",
                "lmName" => "GPTModelTranslate",
                "context" => $this->contextId,
                ]
            )
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        /** @var string */
        $id = $responseContent['data']['conversationId'];
        $this->conversationId = $id;
    }

    public function testControllerRouting(): void
    {
        $this->client->request(
            "GET",
            "/api/v1/conversations",
            [
                "Id" => $this->conversationId,
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
}
