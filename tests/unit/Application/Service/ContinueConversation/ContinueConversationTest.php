<?php

namespace Chatbot\Tests\Application\Service\ContinueConversation;

use Chatbot\Application\Service\ContinueConversation\ContinueConversation;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationRequest;
use Chatbot\Application\Service\ContinueConversation\ContinueConversationResponse;
use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\LanguageModel\Parrot;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use Chatbot\Tests\BaseTestCase;
use PHPUnit\Framework\TestCase;

class ContinueConversationtest extends BaseTestCase
{

    private string $API_KEY;
    
    private ConversationRepositoryInMemory $repository;
    private ConversationId $convid;
    private LanguageModelAbstractFactory $factory;
    public function setUp(): void
    {

        parent::setUp();

        // Initialiser la variable API_KEY à partir de l'environnement
        $this->API_KEY = getenv('API_KEY');

        $this->repository = new ConversationRepositoryInMemory();
        $this->factory = new ModelFactory($this->API_KEY);
        $request = new MakeConversationRequest("Bonjour", "Parrot", "You're helpfull assistant");
        $service = new MakeConversation($this->repository, $this->factory);
        $service->execute($request);
        $response = $service->getResponse();
        $this->convid = new ConversationId($response->conversationId);
    }

    public function testSomeoneContinueAConversation(): void
    {

        //arrange / Given
        $prompt = new ContinueConversationRequest("Bonjour", $this->convid, "Parrot");
        $service = new ContinueConversation($this->repository, $this->factory);
        //act / When
        $token1 = $this->repository->findById($this->convid)->getTotalToken();
        $this->assertGreaterThan(1, $token1);
        $service->execute($prompt);
        //assert /Then
        $token2 = $this->repository->findById($this->convid)->getTotalToken();
        $this->assertGreaterThan($token1, $token2);
        $this->assertInstanceOf(ContinueConversationResponse::class, $service->getResponse());
    }
}
