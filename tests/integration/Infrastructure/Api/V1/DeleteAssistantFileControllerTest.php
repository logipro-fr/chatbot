<?php

namespace Chatbot\Test\integration\Infrastructure\Api\V1;

use Chatbot\Domain\Model\Assistant\Assistant;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Chatbot\Domain\Model\File\FileMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Dotenv\Dotenv;

class DeleteAssistantFileControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private Assistant $assistant;



    public function setup(): void
    {
        $this->initDoctrineTester();

        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/.env.local');
        $this->clearTables(["assistant"]);

        $this->client = static::createClient(['debug' => false]);


        $externalAssistantId = $_ENV['OPENAI_ASSISTANT_ID'];

        if (!is_string($externalAssistantId)) {
            self::fail('OPENAI_ASSISTANT_ID_TEST doit être une string dans .env.test.local');
        }

        $this->assistant = new Assistant(
            new AssistantId(),
            'Assistant Test',
            'Tu es un assistant utile',
            $externalAssistantId
        );

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($this->assistant);
        $em->flush();

        $assistantId = (string) $this->assistant->getAssistantId();

        /** @var non-empty-string $payload */
        $payload = json_encode([
            'file_ids' => ['file-xxxx', 'file-yyyy'],
        ], JSON_THROW_ON_ERROR);


        $this->client->request(
            'PUT',
            "/api/v1/assistant/{$assistantId}/files",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );
    }

    public function testControllerRouting(): void
    {

        $ast_id = $this->assistant->getAssistantId();
        $fil_id = 'file-xxxx';


        /** @var non-empty-string $deletePayload */
        $deletePayload = json_encode([
            "ContextMessage" => "You're helpful assistant",
         ], JSON_THROW_ON_ERROR);



        $this->client->request(
            'DELETE',
            "/api/v1/assistant/{$ast_id}/files/{$fil_id}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $deletePayload
        );


        $response = $this->client->getResponse();
        $content = $response->getContent();

        $this->assertIsString($content);
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            200,
            $response->getStatusCode(),
            "Réponse reçue : {$content}"
        );

        $this->assertIsArray($data);
        $this->assertTrue($data['success']);
    }
}
