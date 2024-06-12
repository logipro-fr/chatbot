<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Api\V1\ChatBotContinueController;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Chatbot\Tests\WebBaseTestCase;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\Cloner\Data;

use function Safe\json_decode;
use function Safe\json_encode;

class ChatBotContinueControllerTest extends WebBaseTestCase
{
    use DoctrineRepositoryTesterTrait;

    private string $API_KEY;
    private KernelBrowser $client;
    private ConversationId $convId;

    public function setUp(): void
    {
        parent::setUp();
        $apiKey = getenv('API_KEY');
        if ($apiKey === false) {
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
        }
        $this->initDoctrineTester();
        //$this->clearTables(['conversations']);
        $this->client = static::createClient(["debug" => false]);

        //$autoInjectedRepo = $this->client->getContainer()->get('conversation.repository');
        //$this->repository = $autoInjectedRepo;
    }

    public function testChatBotControllerExecute(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->API_KEY);
        $request = new MakeConversationRequest("Bonjour", "Parrot", "You're helpfull assistant");
        $service = new MakeConversation($repository, $factory);
        $service->execute($request);
        $response = $service->getResponse();
        $this->convId = new ConversationId($response->conversationId);
        $controller = new ChatBotContinueController($repository, $factory, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversation/Continue",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Prompt" => "Bonjour",
                "convId" => "$this->convId",
                "lmName" => "GPTModelTranslate",
            ])
        );
        $response = $controller->execute($request);
        /** @var string */
        $responseContent = $response->getContent();
        $this->assertJson($responseContent);
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

                "Prompt" => "Bonjour",
                "lmName" => "GPTModelTranslate",
                "context" => "youre helpfull assistant",
            ])
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        /** @var string */
        $id = $responseContent['data']['id'];

        $this->client->request(
            "POST",
            "/api/v1/conversation/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Bonjour",
                "convId" => "$id",
                "lmName" => "GPTModelTranslate",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"id":"con_', $responseContent);
        $this->assertStringContainsString('"nbPair":', $responseContent);
        $this->assertStringContainsString('"lastPair":', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }

    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversation/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Bonjour",
                "convId" => "con_5aez1gf4rz3251vf",
                "lmName" => "GPTModelTranslate",
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
