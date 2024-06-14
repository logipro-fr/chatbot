<?php

namespace Chatbot\Tests\integration\Infrastructure;

use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Infrastructure\Shared\Symfony\Kernel;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class MakeConversationControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    //private ConversationRepositoryInterface $repository;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd().'/src/Infrastructure/Shared/Symfony/.env.local');
        //$this->clearTables(["conversations"]);
        $this->client = self::createClient(["debug" => false]);
        


        //$kernel = new Kernel('test');
       // /** @var ConversationRepositoryDoctrine $autoInjectedRepo */
      //  $autoInjectedRepo = $this->client->getContainer()->get("conversation.repository");
        //$this->repository = $autoInjectedRepo;
    }

    public function testControllerRouting(): void
    {
        //$client = static::createClient();
        $this->client->request(
            "POST",
            "/api/v1/conversation/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "Prompt" => "Chien",
                "lmName" => "GPTModelTranslate",
                "context" => "english",
                ]
            )
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"id":"con_', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
