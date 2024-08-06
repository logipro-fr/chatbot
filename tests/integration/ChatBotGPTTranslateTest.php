<?php

namespace Chatbot\Tests\integration;

use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Dotenv\Dotenv;

class ChatBotGPTTranslateTest extends TestCase
{
    public function setUp(): void
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(getcwd() . '/src/Infrastructure/Shared/Symfony/.env.local');
    }



    public function testTranslate(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory();
        $contextrepository = new ContextRepositoryInMemory();
        $contextrequest= new MakeContextRequest(new ContextMessage("english"), new ContextId("inEnglish"));
        (new MakeContext($contextrepository))->execute($contextrequest);
        $request = new MakeConversationRequest(
            new Prompt("Bonjour, comment ça va?"),
            "GPTModelTranslate",
            new ContextId("inEnglish")
        );
        $service = new MakeConversation($repository, $factory, $contextrepository);
        $service->execute($request);

        $response = $service->getResponse();


        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $pair = $conversation->getPair(0);
        $answer = $pair->getAnswer()->getMessage();
        $this->assertEquals("Hello, how are you?", $answer) ;
    }
}
