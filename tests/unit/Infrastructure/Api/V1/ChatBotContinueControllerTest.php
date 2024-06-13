<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Api\V1\ChatBotContinueController;
use Chatbot\Infrastructure\Api\V1\ChatBotMakeController;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ChatBotContinueControllerTest extends TestCase
{

    private string $API_KEY;

    public function setUp(): void
    {

        parent::setUp();

        // Initialiser la variable API_KEY à partir de l'environnement
        $this->API_KEY = getenv('API_KEY');

    }
    
    public function testChatBotControllerExecute(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->API_KEY);
        $request = new MakeConversationRequest("Bonjour", "Parrot", "You're helpfull assistant");
        $service = new MakeConversation($repository, $factory);
        $service->execute($request);
        $response = $service->getResponse();
        $convid = new ConversationId($response->conversationId);
        $controller = new ChatBotContinueController($repository, $factory);
        $request = Request::create(
            "/api/v1/conversation/Continue",
            "POST",
            [
                "Prompt" => "Bonjour",
                "convId" => "$convid",
                "lmName" => "GPTModelTranslate",
            ]
        );


        $response = $controller->execute($request);
        /** @var string */
        $responseContent = $response->getContent();

        $this->assertStringContainsString('"success":true', $responseContent);
        $this->assertStringContainsString('"statusCode":200', $responseContent);
        $this->assertStringContainsString('"data":"con_', $responseContent);
        $this->assertStringContainsString('"message":"', $responseContent);
    }
}
