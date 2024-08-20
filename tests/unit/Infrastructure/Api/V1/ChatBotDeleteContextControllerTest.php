<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Infrastructure\Api\V1\ChatBotDeleteContextController;
use Chatbot\Infrastructure\Api\V1\ChatBotEditContextController;
use Chatbot\Infrastructure\Exception\NoIdException;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotDeleteContextControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;
    use AssertResponseTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->client = static::createClient(["debug" => false]);
        $this->clearTables(["context"]);
    }

    public function testDeleteContextControllerExecute(): void
    {

        $contextrepo = new ContextRepositoryInMemory();
        $conversationrepo = new ConversationRepositoryInMemory();
        $conversationrepo->add(new Conversation(new PairArray(), new ContextId("english")));
        $controller = new ChatBotDeleteContextController($contextrepo, $conversationrepo, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversation/Delete",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => "base",
            ])
        );
        $response = $controller->deleteContext($request);
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
        $contextid = $responseContent['data']['contextId'];

        $this->client->request(
            "POST",
            "/api/v1/context/Delete",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => $contextid,
            ])
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        
        $this->assertTrue($responseContent["success"]);
        $this->assertEquals(200, $responseCode);
        $this->assertArrayHasKey('message', $responseContent["data"]);
    }

    public function testControllerException(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/context/Delete",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => "Je n'existe pas",
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertResponseFailure(
            $this->client->getResponse(),
            (new \ReflectionClass(NoIdException::class))->getShortName()
        );
    }
}
