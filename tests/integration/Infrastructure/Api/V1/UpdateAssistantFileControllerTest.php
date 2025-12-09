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

        if (!is_string($externalAssistantId)) {
            self::fail('OPENAI_ASSISTANT_ID_TEST doit être une string dans .env.test.local');
        }

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

        $response = $this->client->getResponse();
        $content = $response->getContent();
        if ($content === false) {
            self::fail('Le contenu de la réponse est false, ce qui ne devrait pas arriver.');
        }
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            200,
            $response->getStatusCode(),
            "Réponse reçue : {$content}"
        );

        $this->assertIsArray($data, 'La réponse JSON doit être un array.');
        $this->assertArrayHasKey('success', $data, 'La clé "success" doit être présente dans la réponse.');
        $this->assertTrue($data['success'], 'Le champ "success" doit être à true en cas de succès.');

        $this->assertArrayHasKey('data', $data, 'La clé "data" doit être présente dans la réponse.');
        $this->assertIsArray($data['data'], 'Le champ "data" doit être un array.');
    }
}
