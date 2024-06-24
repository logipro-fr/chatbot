<?php

namespace Chatbot\Tests\integration;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Conversation\Context;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\CurlHttpClient;

class MakeConversationTest extends TestCase
{
    public function testMakeOneConversation(): void
    {
        $repository = new ConversationRepositoryInMemory();
        $client = new CurlHttpClient();
        $factory = new ModelFactory();
        $context = new Context("You're a helpfull assistant assistant");
        $request = new MakeConversationRequest(new Prompt("Bonjour, comment vas tu ?"), "GPTModel", $context);
        $service = new MakeConversation($repository, $factory);
        $service->execute($request);

        $response = $service->getResponse();


        $conversation = $repository->findById(new ConversationId($response->conversationId));
        $pair = $conversation->getPair(0);
        $responseMessage = $pair->getAnswer()->getMessage();

        $this->assertEquals(true, is_string($responseMessage));
        $prompt = new Prompt("Ca va super ! Quel temps fait il chez toi ?");
        $id = new ConversationId($response->conversationId) ;
        $request = new ContinueConversationRequest($prompt, $id, "GPTModel");
        $service = new ContinueConversation($repository, $factory);
        $service->execute($request);
        $pair = $conversation->getPair(1);
        $responseMessage = $pair->getAnswer()->getMessage();

        $this->assertEquals(true, is_string($responseMessage));
    }
}
