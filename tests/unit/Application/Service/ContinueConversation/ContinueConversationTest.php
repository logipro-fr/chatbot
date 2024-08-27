<?php

namespace Chatbot\Tests\Application\Service\ContinueConversation;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationResponse;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use PHPUnit\Framework\TestCase;

class ContinueConversationtest extends TestCase
{
    use DoctrineRepositoryTesterTrait;

    private ConversationRepositoryInterface $repository;
    private ConversationId $convid;
    private LanguageModelAbstractFactory $factory;
    private ContextRepositoryInMemory $contextrepo;
    public function setUp(): void
    {
        $this->initDoctrineTester();
        $this->clearTables(["conversations_pairs","conversations","pairs"]);

        $this->repository = new ConversationRepositoryDoctrine($this->getEntityManager());
        $this->factory = new ModelFactory();
        $this->contextrepo = new ContextRepositoryInMemory();
        $request = new MakeConversationRequest(
            new Prompt("Bonjour"),
            "Parrot",
            new ContextId("base")
        );
        $service = new MakeConversation($this->repository, $this->factory, $this->contextrepo);
        $service->execute($request);
        $response = $service->getResponse();
        $this->convid = new ConversationId($response->conversationId);
    }

    public function testSomeoneContinueAConversation(): void
    {

        //arrange / Given
        $prompt = new ContinueConversationRequest(new Prompt("Bonsoir"), $this->convid, "Parrot");
        $service = new ContinueConversation($this->repository, $this->factory);
        //act / When
        $nbPair = $this->repository->findById($this->convid)->countPair();
        $this->assertEquals(1, $nbPair);
        $service->execute($prompt);
        //assert /Then
        $conversation = $this->repository->findById($this->convid);
        $nbPair2 = $conversation->countPair();
        $this->assertEquals(2, $nbPair2);
        $this->assertInstanceOf(ContinueConversationResponse::class, $service->getResponse());
    }

    public function testConversationWithThreePairs(): void
    {
        //arrange / Given
        $prompt = new ContinueConversationRequest(new Prompt("Bonsoir"), $this->convid, "Parrot");
        $service = new ContinueConversation($this->repository, $this->factory);
        //act / When
        $nbPair = $this->repository->findById($this->convid)->countPair();
        $this->assertEquals(1, $nbPair);
        $service->execute($prompt);
        $service->execute($prompt);
        //assert /Then
        $conversation = $this->repository->findById($this->convid);
        $nbPair2 = $conversation->countPair();
        $this->assertEquals(3, $nbPair2);
        $this->assertInstanceOf(ContinueConversationResponse::class, $service->getResponse());
    }
}
