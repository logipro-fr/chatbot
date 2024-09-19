<?php

namespace Chatbot\Tests\integration\Infrastructure;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Phariscope\MultiTenant\Doctrine\DatabaseTools;
use Phariscope\MultiTenant\Doctrine\EntityManagerResolver;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

use function Safe\json_encode;
use function SafePHP\strval;

class MakeContextControllerWithTenantTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->initDoctrineTester();
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
        $this->clearTables(["context"]);
        $this->client = self::createClient(["debug" => false]);
    }

    public function testControllerRouting(): void
    {
        $tenantId = "tenant1234";

        $emTenant = (new EntityManagerResolver($this->getEntityManager()))->getEntityManager($tenantId);
        $tools = new DatabaseTools();
        if ($tools->databaseExists($emTenant)) {
            $tools->dropDatabase($emTenant);
        }

        $this->client->request(
            "POST",
            "/api/v1/context/Make?tenant_id=$tenantId",
            [
            ],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(
                [
                "ContextMessage" => "I wish to to talk to tenant $tenantId",
                ]
            )
        );
        /** @var string */
        $data = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        /** @var array<mixed,array<mixed>> */
        $responseContent = json_decode($data, true);

        $this->assertTrue($responseContent["success"]);
        $this->assertEquals(200, $responseCode);
        $this->assertArrayHasKey("contextId", $responseContent["data"]);

        $em = (new EntityManagerResolver($this->getEntityManager()))->getEntityManager($tenantId);
        $repository = new ContextRepositoryDoctrine($em);
        $contextId = strval($responseContent["data"]["contextId"]);
        $context = $repository->findById(new ContextId($contextId));
        $this->assertIsObject($context);
    }
}
