<?php

namespace Features;

use Behat\Behat\Context\Context;
use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Context\Context as ConversationContext;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\Assert;

/**
 * Defines applaication features from the specific context.
 */
class QueryModelContext implements Context
{
    private MakeConversationResponse $response ;
    private Conversation $conversation;
    private ConversationRepositoryInterface $repository;
    private ContextRepositoryInterface $contextrepo;
    private int $numberOfPairs;
    private string $lmName;

    public function __construct()
    {
    }


 /**
     * @Given I want to speak with the langage model :prompt
     */
    public function iWantToSpeakWithTheLangageModel(string $model): void
    {
        $this->lmName = $model;
    }

    /**
     * @When I start a conversation prompting with :prompt
     */
    public function iStartAConversationPromptingWith(string $prompt): void
    {
        $request = new MakeConversationRequest(
            new Prompt($prompt),
            $this->lmName,
            new ContextId("base")
        );
        $this->repository = new ConversationRepositoryInMemory();
        $this->contextrepo = new ContextRepositoryInMemory();
        $factory = new ModelFactory();
        $service = new MakeConversation($this->repository, $factory, $this->contextrepo);
        $service->execute($request);
        $this->response = $service->getResponse();
    }

    /**
     * @Then I get an answer :answer
     */
    public function iGetAnAnswer(string $answer): void
    {
        /** @var Conversation $conversation */
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        $this->conversation = $conversation;
        $pair = $this->conversation->getpair(0);
        $response = $pair->getAnswer()->getMessage();
        Assert::assertEquals($answer, $response);
    }

    /**
     * @Then I have a conversation identifier
     */
    public function iHaveAConversationIdentifier(): void
    {
        Assert::assertNotEmpty($this->response->conversationId);
    }

     /**
     * @Given I started a conversation
     */
    public function iStartedAConversation(): void
    {
        $request = new MakeConversationRequest(
            new Prompt("Bonjour"),
            "Parrot",
            new ContextId("base")
        );
        $this->repository = new ConversationRepositoryInMemory();
        $this->contextrepo = new ContextRepositoryInMemory();
        $factory = new ModelFactory();
        $service = new MakeConversation($this->repository, $factory, $this->contextrepo);
        $service->execute($request);
        $this->response = $service->getResponse();
        $this->conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        //$this->tokencount1 = $this->conversation->getTotalToken();
    }

    /**
     * @When I ask :prompt
     */
    public function iAsk(string $prompt): void
    {
        $factory = new ModelFactory();
        $id = new ConversationId($this->response->conversationId);
        $request = new ContinueConversationRequest(new Prompt($prompt), $id, "Parrot");
        $service = new ContinueConversation($this->repository, $factory);
        $service->execute($request);
    }

    /**
     * @Then I have an answer
     */
    public function iHaveAnAnswer(): void
    {
        /** @var Conversation $conversation */
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        $pair = $conversation->getpair(1);
        $response = $pair->getAnswer()->getMessage();
        Assert::assertNotEmpty($response);
    }

    /**
     * @Then the number of token has increased
     */
    public function theNumberOfTokenHasIncreased(): void
    {
        /** @var Conversation $conversation */
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
       // Assert::assertGreaterThan($this->tokencount1, $conversation->getTotalToken());
    }

     /**
     * @Given I have an existing conversation
     */
    public function iHaveAnExistingConversation(): void
    {
        $this->repository = new ConversationRepositoryInMemory();
        $this->contextrepo = new ContextRepositoryInMemory();

        $request = new MakeConversationRequest(new Prompt("Bonjour"), "GPTModel", new ContextId("base"));
        $factory = new ModelFactory();
        $service = new MakeConversation($this->repository, $factory, $this->contextrepo);
        $service->execute($request);
        $response = $service->getResponse();

        $this->conversation = $this->repository->findById(new ConversationId($response->conversationId));
        $pair = $this->conversation->getPair(0);
        $responseMessage = $pair->getAnswer()->getMessage();

        $this->numberOfPairs = $this->conversation->countPair();
    }


    /**
     * @When I request :prompt
     */
    public function iRequest(string $prompt): void
    {
        $factory = new ModelFactory();
        $service = new ContinueConversation($this->repository, $factory);
        $request = new ContinueConversationRequest(
            new Prompt($prompt),
            $this->conversation->getConversationId(),
            "GPTModel"
        );
        $service->execute($request);
    }

    /**
     * @Then I should get an answer :answer
     */
    public function iShouldGetAnAnswer(string $answer): void
    {
        $response = $this->conversation->getPair(1)->getAnswer()->getMessage();
        Assert::assertEquals($answer, $response);
    }

    /**
     * @Then the conversation is enriched by a new pair
     */
    public function theConversationIsEnrichedByANewPair(): void
    {
        Assert::assertGreaterThan($this->numberOfPairs, $this->conversation->countPair());
    }
}
