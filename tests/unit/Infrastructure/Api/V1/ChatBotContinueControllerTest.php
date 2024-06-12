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
            var_dump(false);
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
        }
        $this->initDoctrineTester();
        //$this->clearTables(['conversations']);
        $this->client = static::createClient(["debug" => false]);
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

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertStringContainsString('"statusCode":200', $responseContent);
        $this->assertStringContainsString('"data":"con_', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }

    public function testControllerRouting(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversation/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Bonjour",
                "convId" => "con_6661821d0f85a",
                "lmName" => "GPTModelTranslate",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
    }
}
