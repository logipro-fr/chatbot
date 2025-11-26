<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;

class ConversationContext implements Context
{
    private string $languageModelName = "";
    private ConversationRepositoryInterface $repository;
    private ContextRepositoryInterface $contextrepo;
    private MakeConversationRequest $request;
    private MakeConversation $service;

    public function __construct()
    {
    }

     #[Given('the assistant use model :name')]
    public function theAssistantUseModel(string $name): void
    {
        $this->languageModelName = $name;
    }


    #[Given('User prepares the request with this prompt :askquestion to the assistant')]
    public function userPreparesTheRequestWithThisPromptToTheAssistant(string $askquestion): void
    {
        $this->request = new MakeConversationRequest(
            new Prompt($askquestion),
            $this->languageModelName,
            new ContextId("base")
        );
    }

    #[When('the assistant receives the prompt')]
    public function theAssistantReceivesThePrompt(): void
    {
        $this->repository = new ConversationRepositoryInMemory();
        $this->contextrepo = new ContextRepositoryInMemory();
        $factory = new ModelFactory();
        $this->service = new MakeConversation($this->repository, $factory, $this->contextrepo);
        $this->service->execute($this->request);
    }

    #[Then('the assistant responds :answer')]
    public function theAssistantResponds(string $answer): void
    {
        $response = $this->service->getResponse();
        Assert::assertEquals($answer, $response->botMessage);
    }
}
