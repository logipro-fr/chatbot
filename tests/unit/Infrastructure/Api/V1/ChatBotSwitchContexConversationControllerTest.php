<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Infrastructure\Api\V1\ChatBotSwitchController;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotSwitchContexConversationControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->clearTables(["conversations"]);
        $this->client = static::createClient(["debug" => false]);
    }

    public function testEditContextControllerExecute(): void
    {

        $repository = new ConversationRepositoryInMemory();
        $repository->add(
            new Conversation(new PairArray(), new ContextId("base"), new ConversationId("conversation_id"))
        );
        $controller = new ChatBotSwitchController($repository, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversations/SwitchContext",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ConversationId" => "conversation_id",
                "ContextId" => "id_modified",
            ])
        );
        $response = $controller->switchContextConversation($request);
        /** @var string */
        $responseContent = $response->getContent();
        $this->assertJson($responseContent);
    }

    public function testControllerRouting(): void
    {

        $this->client->request(
            "POST",
            "/api/v1/context/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ContextMessage" => "je suis un context",
            ])
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        $contextid = $responseContent['data']['id'];

        $this->client->request(
            "POST",
            "/api/v1/conversations/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([

                "Prompt" => "Chien",
                "lmName" => "ParrotTranslate",
                "context" => $contextid,
            ])
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        $conversationid = $responseContent['data']['id'];

        $this->client->request(
            "POST",
            "/api/v1/conversations/SwitchContext",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "ConversationId" => $conversationid,
                "ContextId" => "id_modified",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"NewId":"', $responseContent);
        $this->assertStringContainsString('"ConversationId":"', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
