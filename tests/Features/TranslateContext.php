<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Application\Service\MakeTranslate\MakeTranslate;
use Chatbot\Application\Service\MakeTranslate\MakeTranslateRequest;
use Chatbot\Application\Service\MakeTranslate\MakeTranslateResponse;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\GPTModelTranslate;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\Assert;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Defines applaication features from the specific context.
 */
class translateContext implements Context
{
    private ConversationRepositoryInMemory $repository;
    private MakeConversationResponse $response;
    private string $lang;
    private LanguageModelAbstractFactory $factory;
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct()
    {
        // Chargez le fichier .env.test
        $dotenv = new Dotenv();
        $dotenv->usePutenv();
        $dotenv->load(__DIR__ . '/../.env.test');

        // Récupérer la variable d'environnement
        $apiKey = getenv('API_KEY');

        if ($apiKey === false) {
            var_dump(false);
            throw new \RuntimeException('API_KEY environment variable is not set.');
        } else {
            $this->apiKey = $apiKey;
        }
    }

    /**
     * @Given I want to translate in language :lang with the language model :arg2
     */
    public function iWantToTranslateInLanguageWithTheLanguageModel(string $lang, string $arg2): void
    {
        $this->repository = new ConversationRepositoryInMemory();
        $this->client = new CurlHttpClient();
        $this->factory = new ModelFactory($this->apiKey);
        $this->lang = $lang;
    }



    /**
     * @When I prompt :prompt
     */
    public function iPrompt(string $prompt): void
    {
        $request = new MakeConversationRequest($prompt, "GPTModelTranslate", $this->lang, $this->client);
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
       // var_dump($answer);
        Assert::assertEquals($answerExpected, $answer) ;
    }
}
