<?php

namespace Chatbot\Tests\integration\Infrastructure;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class ContinueConversationControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private string $conversationId;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
        $this->clearTables(["conversations"]);
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
        $contextId = $responseContent['data']['contextId'];

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
                "context" => $contextId,
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
            "POST",
            "/api/v1/conversations/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "Prompt" => "Tu peux me repeter ce que tu as dit avant ?",
                "convId" => $this->conversationId,
                "lmName" => "GPTModel",
                ]
            )
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);

        $this->assertTrue($responseContent["success"]);
        $this->assertArrayHasKey("conversationId", $responseContent["data"]);
        $this->assertArrayHasKey("numberOfPairs", $responseContent["data"]);
        $this->assertArrayHasKey("botMessage", $responseContent["data"]);
    }
}
