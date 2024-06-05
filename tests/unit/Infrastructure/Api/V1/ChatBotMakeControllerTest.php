<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\Api\V1\ChatBotMakeController;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ChatBotMakeControllerTest extends WebTestCase
{

    use DoctrineRepositoryTesterTrait;
    private KernelBrowser $client;
    private ConversationRepositoryInterface $repository;
    private string $API_KEY;

    
    
    public function setUp(): void
    {
        parent::setUp();
        $this->API_KEY = getenv('API_KEY');
        $this->initDoctrineTester();
        $this->clearTables(['conversations']);
        $this->client = static::createClient(["debug" => false]);

        $autoInjectedRepo = $this->client->getContainer()->get('conversation.repository');
        $this->repository = $autoInjectedRepo;
    }
    public function testControllerRouting(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversation/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
            
                "Prompt" => "Chien",
                "lmName" => "GPTModelTranslate",
                "context" => "english",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
    }


    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversation/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
            
                "Prompt" => "Chien",
                "lmName" => "",
                "context" => "english",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString('"success":false', $responseContent);
        $this->assertStringContainsString('"statusCode":', $responseContent);
        $this->assertStringContainsString('"data":"', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }


    public function testChatBotControllerExecute(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->API_KEY);
        $controller = new ChatBotMakeController($repository, $factory, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversation/make",
            "GET",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Prompt" => "Chien",
                "lmName" => "GPTModelTranslate",
                "context" => "english",
            ])
        );


        $response = $controller->execute($request);
        /** @var string */
        $responseContent = $response->getContent();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertStringContainsString('"statusCode":200', $responseContent);
        $this->assertStringContainsString('"data":"con_', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
