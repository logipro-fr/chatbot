<?php

namespace Chatbot\Tests\integration\Infrastructure;

use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

class UpdateAssistantFileControllerTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;
    private Assistant $assistant;
    private AssistantId $assistantId;

    protected function setUp(): void
    {
        parent::setUp();

         $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/.env.local');


        $this->client = static::createClient(['debug' => false]);

        $this->initDoctrineTester();
        $this->clearTables(['assistants', 'files']);

        //Mettre l'id d'un vrai assistant d'OpenIA pour faire le test
        $externalAssistantId = $_ENV['OPENAI_ASSISTANT_ID'];

        $this->assistantId = new AssistantId();
        $this->assistant = new Assistant(
            $this->assistantId,
            'Assistant Test',
            'Tu es un assistant utile',
            $externalAssistantId
        );

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($this->assistant);
        $em->flush();
    }

    public function testControllerRouting(): void
    {
        $assistantId = (string) $this->assistant->getAssistantId();

        $this->client->request(
            'PUT',
            "/api/v1/assistant/{$assistantId}/files",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'file_ids' => ['file-1', 'file-2'],
            ])
        );

        $response = $this->client->getResponse();
        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->assertSame(
            200,
            $response->getStatusCode(),
            "Réponse reçue : {$content}"
        );

        $this->assertTrue($data['success']);
    }
}
