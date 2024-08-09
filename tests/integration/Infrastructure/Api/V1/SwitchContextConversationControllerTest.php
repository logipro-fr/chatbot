<?php

namespace Chatbot\Tests\integration\Infrastructure;

use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class SwitchContextConversationControllerTest extends WebTestCase
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
            "/api/v1/conversations/SwitchContext",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ConversationId" => "con_66b49782713ab",
                "ContextId" => "Ne",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"NewId":"id_modified"', $responseContent);
        $this->assertStringContainsString('"ConversationId":"', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }

   
}
