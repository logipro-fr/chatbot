<?php 

namespace Chatbot\Tests\integration\Infrastructure;

use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MakeConversationControllerTest extends WebTestCase{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private ConversationRepositoryInterface $repository;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["conversations"]);
        $this->client = self::createClient(["debug"=>false]);
        /** @var ConversationRepositoryDoctrine $autoInjectedRepo */
        $autoInjectedRepo = $this->client->getContainer()->get("conversation.repository");
        $this->repository = $autoInjectedRepo;
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
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertStringContainsString('"statusCode":200', $responseContent);
        $this->assertStringContainsString('"data":"con_', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}