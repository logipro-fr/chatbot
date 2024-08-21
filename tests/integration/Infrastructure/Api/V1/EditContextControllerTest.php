<?php

namespace Chatbot\Tests\integration\Infrastructure;

use Chatbot\Domain\Model\Context\ContextId;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class EditContextControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private string $contextId;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
        $this->clearTables(["context"]);
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
        $this->contextId = $id ;
    }

    public function testControllerRouting(): void
    {
        $this->client->request(
            "PUT",
            "/api/v1/contexts",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "Id" => $this->contextId,
                "NewMessage" => "English",
                ]
            )
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
}
