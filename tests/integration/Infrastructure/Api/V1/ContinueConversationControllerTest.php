<?php

namespace Chatbot\Tests\integration\Infrastructure;

use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;

class ContinueConversationControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private string $conversationId;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
        $this->clearTables(["conversations_pairs", "pairs", "conversations"]);
        $this->client = self::createClient(["debug" => false]);

        $this->client->request(
            "POST",
            "/api/v1/context/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "ContextMessage" => "You're helpfull asistant",
                ]
            )
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        $contextId = $responseContent['data']['contextId'];

        $this->client->request(
            "POST",
            "/api/v1/conversations/Make",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "Prompt" => "Je m'appelle Marine",
                "lmName" => "GPTModel",
                "context" => $contextId,
                ]
            )
        );

        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        /** @var string */
        $id = $responseContent['data']['conversationId'];
        $this->conversationId = $id;
    }

    public function testControllerRouting(): void
    {
        $this->client->request(
            "POST",
            "/api/v1/conversations/Continue",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "Prompt" => "Comment je m'appelle ?",
                "convId" => $this->conversationId,
                "lmName" => "GPTModel",
                ]
            )
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);
        /** @var string */
        $botMessage = $responseContent['data']['botMessage'];
        $this->assertTrue($responseContent["success"]);
        $data = $responseContent["data"];
        $this->assertArrayHasKey("conversationId", $data);
        $this->assertEquals(2, $data["numberOfPairs"]);
        $this->assertArrayHasKey("botMessage", $responseContent["data"]);
        $this->assertStringContainsStringIgnoringCase("Marine", $botMessage);

        $this->assertConversationPairCountInRepository(2);
    }

    private function assertConversationPairCountInRepository(int $expectedCount): void
    {
        $conversationRepository = new ConversationRepositoryDoctrine($this->getEntityManager());
        $conversation = $conversationRepository->findById(new ConversationId($this->conversationId));
        $this->assertEquals($expectedCount, $conversation->countPair());
    }
}
