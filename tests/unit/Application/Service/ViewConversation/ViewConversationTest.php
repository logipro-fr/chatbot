<?php

namespace Chatbot\Tests\Application\Service\ViewContext;


use Chatbot\Application\Service\MakeConversation\LanguageModelAbstractFactory;
use Chatbot\Application\Service\MakeConversation\MakeConversation;
use Chatbot\Application\Service\MakeConversation\MakeConversationRequest;
use Chatbot\Application\Service\ViewConversation\ViewConversation;
use Chatbot\Application\Service\ViewConversation\ViewConversationRequest;
use Chatbot\Application\Service\ViewConversation\ViewConversationResponse;
use Chatbot\Domain\Model\Context\ContextId;;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\ConversationRepositoryInterface;
use Chatbot\Domain\Model\Conversation\Prompt;
use Chatbot\Infrastructure\LanguageModel\ModelFactory;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class ViewConversationTest extends TestCase
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

    public function testFindContextWithContextId(): void
    {
        $service = new ViewConversation($this->repository);
        $service->execute(new ViewConversationRequest($this->convid));
        $response = $service->getResponse();
        $this->assertInstanceOf(ViewConversationResponse::class, $response);
        $this->assertIsString($response->contextId);

    }
}
