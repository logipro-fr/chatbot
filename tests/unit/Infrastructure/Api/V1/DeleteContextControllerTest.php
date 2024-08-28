<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Infrastructure\Api\V1\DeleteContextController;
use Chatbot\Infrastructure\Api\V1\EditContextController;
use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class DeleteContextControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;
    use AssertResponseTrait;

    private KernelBrowser $client;

    private string $contextId;

    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->client = static::createClient(["debug" => false]);
        $this->clearTables(["context", "conversations", "conversations_pairs", "pairs"]);
    }

    public function testDeleteContextControllerExecute(): void
    {

        $contextrepo = new ContextRepositoryInMemory();
        $conversationrepo = new ConversationRepositoryInMemory();
        $conversationrepo->add(new Conversation(new ContextId("english")));
        $controller = new DeleteContextController($contextrepo, $conversationrepo, $this->getEntityManager());
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

        $this->initializeContextWithRouting();

        $this->client->request(
            "DELETE",
            "/api/v1/contexts",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => $this->contextId,
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
            "DELETE",
            "/api/v1/contexts",
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
            (new \ReflectionClass(ContextNotFoundException::class))->getShortName()
        );
    }

    private function initializeContextWithRouting(): void
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
        $this->contextId = strval($responseContent['data']['contextId']);
    }
}
