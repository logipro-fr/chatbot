<?php

namespace Features;

use Behat\Behat\Context\Context;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Conversation\Context as ConversationContext;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
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




    /**
     * @Given I want to translate in language :lang with the language model :arg2
     */
    public function iWantToTranslateInLanguageWithTheLanguageModel(string $lang, string $arg2): void
    {
        $this->repository = new ConversationRepositoryInMemory();
        $this->factory = new ModelFactory();
        $this->lang = $lang;
    }



    /**
     * @When I prompt :prompt
     */
    public function iPrompt(string $prompt): void
    {
        $request = new MakeConversationRequest(
            new Prompt($prompt),
            "ParrotTranslate",
            new ConversationContext($this->lang)
        );
        $service = new MakeConversation($this->repository, $this->factory);
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
