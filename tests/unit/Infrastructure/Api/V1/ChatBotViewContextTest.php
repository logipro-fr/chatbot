<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\ChatBotViewContextController;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

use function Safe\json_encode;

class ChatBotViewContextTest extends WebTestCase
{
    use DoctrineRepositoryTesterTrait;

    private KernelBrowser $client;


    public function setUp(): void
    {

        $this->initDoctrineTester();
        $this->client = static::createClient(["debug" => false]);
    }

    public function testViewContextControllerExecute(): void
    {

        $contextrepo = new ContextRepositoryInMemory();
        $controller = new ChatBotViewContextController($contextrepo, $this->getEntityManager());
        $request = Request::create(
            "/api/v1/conversation/View",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => "base",
            ])
        );
        $response = $controller->viewContext($request);
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
            "/api/v1/context/View",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                "Id" => $contextid,
            ])
        );
        /** @var string */
        $responseContent = $this->client->getResponse()->getContent();
        $responseCode = $this->client->getResponse()->getStatusCode();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertEquals(200, $responseCode);
        $this->assertStringContainsString('"context":"je suis un context', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
//
//
//    public function testControllerException(): void
//    {
//        $this->client->request(
//            "POST",
//            "/api/v1/conversation/Make",
//            [],
//            [],
//            ['CONTENT_TYPE' => 'application/json'],
//            json_encode([
//
//                "Prompt" => "Chien",
//                "lmName" => "",
//                "context" => "base",
//            ])
//        );
//        /** @var string */
//        $responseContent = $this->client->getResponse()->getContent();
//        $responseCode = $this->client->getResponse()->getStatusCode();
//        $this->assertResponseIsSuccessful();
//
//        $this->assertStringContainsString('"success":false', $responseContent);
//        $this->assertEquals(200, $responseCode);
//        $this->assertStringContainsString('"data":"', $responseContent);
//        $this->assertStringContainsString('"message":"', $responseContent);
//    }
}
