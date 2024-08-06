<?php

namespace Features;

use Behat\Behat\Context\Context;
use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Context\Context as ContextContext;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\Assert;

/**
 * Defines applaication features from the specific context.
 */
class TranslateContext implements Context
{
    private ConversationRepositoryInMemory $repository;
    private MakeConversationResponse $response;
    private string $lang;
    private LanguageModelAbstractFactory $factory;
    private ContextRepositoryInterface $contextrepo;




    /**
     * @Given I want to translate in language :lang with the language model :arg2
     */
    public function iWantToTranslateInLanguageWithTheLanguageModel(string $lang, string $arg2): void
    {
        $this->repository = new ConversationRepositoryInMemory();
        $this->factory = new ModelFactory();
        $this->lang = $lang;
        $this->contextrepo = new ContextRepositoryInMemory();
    }



    /**
     * @When I prompt :prompt
     */
    public function iPrompt(string $prompt): void
    {
        $contextservice = new MakeContext($this->contextrepo);
        $contextservice->execute(new MakeContextRequest(new ContextMessage($this->lang)));
        $response = $contextservice->getResponse();
 
        $request = new MakeConversationRequest(
            new Prompt($prompt),
            "ParrotTranslate",
            $response->contextId
        );
        $service = new MakeConversation($this->repository, $this->factory, $this->contextrepo);
        $service->execute($request);
        $this->response = $service->getResponse();
    }

    /**
     * @Then I get a translation :answerExpected
     */
    public function iGetATranslation(string $answerExpected): void
    {
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        $pair = $conversation->getPair(0);
        $answer = $pair->getAnswer()->getMessage();

        Assert::assertEquals($answerExpected, $answer) ;
    }
}
