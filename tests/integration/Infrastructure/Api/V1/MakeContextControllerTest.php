<?php

namespace Chatbot\Tests\integration\Infrastructure;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class MakeContextControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
        //$this->clearTables(["context"]);
        $this->client = self::createClient(["debug" => false]);
    }

    public function testControllerRouting(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/context/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "ContextMessage" => "English",
                ]
            )
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"id":"cot_', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
