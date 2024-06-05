<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\Exception\EmptyString;
use Chatbot\Application\Service\Exception\ErrorStatusCode;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModel;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\LanguageModel\Parrot;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Chatbot\Tests\Domain\LanguageModelFake;
use PHPUnit\Framework\Assert;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\file_get_contents;

/**
 * Defines applaication features from the specific context.
 */
class QueryModelContext implements Context
{
    private MakeConversationResponse $response ;
    private Conversation $conversation;
    private ConversationRepositoryInterface $repository;
    private int $nbPair;
    private string $lmName;
    private HttpClientInterface $client;
    private int $tokencount1;
    private string $apiKey;

    public function __construct()
    {
        // Chargez le fichier .env.test
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env.test');

        // Récupérer la variable d'environnement
        $this->apiKey = getenv('API_KEY');
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
        $request = new MakeConversationRequest($prompt, $this->lmName, "you're helpfull assitant");
        $this->repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->apiKey);
        $service = new MakeConversation($this->repository, $factory);
        $service->execute($request);
        $this->response = $service->getResponse();
    }

    /**
     * @Then I get an answer :answer
     */
    public function iGetAnAnswer(string $answer): void
    {
        $this->conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
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
        $request = new MakeConversationRequest("Bonjour", "Parrot", "you're helpfull assitant");
        $this->repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->apiKey);
        $service = new MakeConversation($this->repository, $factory);
        $service->execute($request);
        $this->response = $service->getResponse();
        $this->conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        $this->tokencount1 = $this->conversation->getTotalToken();
    }

    /**
     * @When I ask :prompt
     */
    public function iAsk(string $prompt): void
    {
        $factory = new ModelFactory($this->apiKey);
        $request = new ContinueConversationRequest($prompt, new ConversationId($this->response->conversationId), "Parrot");
        $service = new ContinueConversation($this->repository, $factory);
        $service->execute($request);
    }

    /**
     * @Then I have an answer
     */
    public function iHaveAnAnswer(): void
    {
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
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        Assert::assertGreaterThan($this->tokencount1, $conversation->getTotalToken());
    }


    /**
     * @When I submit a POST request to :arg1 with the {:arg2 payload: :prompt }
     */
    public function iSubmitAPostRequestToWithThePayload(string $arg1, string $arg2, string $prompt): void
    {
        $request = new MakeConversationRequest($prompt, "Parrot", "you're helpfull assitant");
        $this->repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->apiKey);
        $service = new MakeConversation($this->repository, $factory);
        $service->execute($request);
        $this->response = $service->getResponse();
    }


    /**
     * @Then I get an answer :arg1 OK
     */
    public function iGetAnAnswerOk(string $arg1): void
    {
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        $pair = $conversation->getpair(0);
        $response = $pair->getAnswer()->getCodeStatus();
        Assert::assertEquals(200, $response);
    }


    /**
     * @Then the response body contains a response generated by the model
     */
    public function theResponseBodyContainsAResponseGeneratedByTheModel(): void
    {
        $conversation = $this->repository->findById(new ConversationId($this->response->conversationId));
        $pair = $conversation->getpair(0);
        $response = $pair->getAnswer()->getMessage();
        Assert::assertNotEmpty($response);
    }


    /**
     * @When I submit two POST requests to :arg1 with the payload {:arg2: :prompt1 } and {:arg4: :prompt2}
     */
    public function iSubmitTwoPostRequestsToWithThePayloadAnd(string $arg1, string $arg2, string $prompt1, string $arg4, string $prompt2): void
    {
        $request = new MakeConversationRequest($prompt1, "Parrot", "you're helpfull assitant");
        $this->repository = new ConversationRepositoryInMemory();
        $factory = new ModelFactory($this->apiKey);
        $service = new MakeConversation($this->repository, $factory);
        $service->execute($request);
        $this->response = $service->getResponse();
        $request = new ContinueConversationRequest($prompt2, new ConversationId($this->response->conversationId), "Parrot");
        $service = new ContinueConversation($this->repository, $factory);
        $service->execute($request);
    }

    /**
     * @Then I get two answers :arg1 OK
     */
    public function iGetTwoAnswersOk(string $arg1): void
    {
        $this->conversation = $this->repository->findById(new ConversationId($this->response->conversationId));

        Assert::assertNotEmpty($this->conversation->getPair(0)->getAnswer()->getCodeStatus());
        Assert::assertNotEmpty($this->conversation->getPair(1)->getAnswer()->getCodeStatus());
    }

    /**
     * @Then the body of the response contains the two responses generated by the model
     */
    public function theBodyOfTheResponseContainsTheTwoResponsesGeneratedByTheModel(): void
    {
        Assert::assertNotEmpty($this->conversation->getPair(0)->getAnswer()->getMessage());
        Assert::assertNotEmpty($this->conversation->getPair(1)->getAnswer()->getMessage());
    }

     /**
     * @Given I have an existing conversation
     */
    public function iHaveAnExistingConversation(): void
    {
        $this->repository = new ConversationRepositoryInMemory();
        $this->client = $this->createMockHttpClient("responseGETSignature.json", 200) ;
        $context = "You're a helpfull assistant ";

        $request = new MakeConversationRequest("Bonjour", "GPTModel", $context, $this->client);
        $factory = new ModelFactory($this->apiKey);
        $service = new MakeConversation($this->repository, $factory);
        $service->execute($request);
        $response = $service->getResponse();

        $this->conversation = $this->repository->findById(new ConversationId($response->conversationId));
        $pair = $this->conversation->getPair(0);
        $responseMessage = $pair->getAnswer()->getMessage();
        //var_dump($responseMessage);
        $this->nbPair = $this->conversation->getNbPair();
    }

    private function createMockHttpClient(string $filename, int $code): MockHttpClient
    {
        $responses = [
            new MockResponse(file_get_contents(__DIR__ . '/ressources/responseGETbonjour.json'), ['http_code' => $code]),
            new MockResponse(file_get_contents(__DIR__ . '/ressources/' . $filename), ['http_code' => $code]),
        ];

        return new MockHttpClient($responses, 'https://api.openai.com/v1/chat/completion');
    }

    /**
     * @When I request :prompt
     */
    public function iRequest(string $prompt): void
    {
        $factory = new ModelFactory($this->apiKey);
        $service = new ContinueConversation($this->repository, $factory);
        $request = new ContinueConversationRequest($prompt, $this->conversation->getId(), "GPTModel", $this->client);
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
        Assert::assertGreaterThan($this->nbPair, $this->conversation->getNbPair());
    }
}
