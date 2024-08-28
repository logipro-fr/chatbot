<?php

namespace Chatbot\Tests\Application\Service\DeleteContext;

use Chatbot\Application\Service\DeleteContext\DeleteContext;
use Chatbot\Application\Service\DeleteContext\DeleteContextRequest;
use Chatbot\Application\Service\DeleteContext\DeleteContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Infrastructure\Exception\ContextNotFoundException;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class DeleteContextTest extends TestCase
{
    public function testSomeoneDeleteAContext(): void
    {
        // arrange / Given

        $conv = new ConversationRepositoryInMemory();
        $conv->add(
            new Conversation(new ContextId("un_context"), new ConversationId("conversation_id"))
        );
        $repository = new ContextRepositoryInMemory();
        $request = new DeleteContextRequest(
            new ContextId("base")
        );
        $service = new DeleteContext($repository, $conv);


        $service->execute($request);
        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(DeleteContextResponse::class, $response);
        $this->expectException(ContextNotFoundException::class);
        $repository->findById(new ContextId("un_context"));
    }

    public function testSomeoneDeleteAContextAssociatedConversation(): void
    {
        // arrange / Given

        $conv = new ConversationRepositoryInMemory();
        $conv->add(new Conversation(new ContextId("base"), new ConversationId("conversation_id")));
        $repository = new ContextRepositoryInMemory();
        $request = new DeleteContextRequest(
            new ContextId("base")
        );
        $service = new DeleteContext($repository, $conv);
        //assert / Then

        $service->execute($request);
        $response = $service->getResponse();
        $this->assertInstanceOf(DeleteContextResponse::class, $response);
        $this->assertStringContainsString(
            "the context can't be deleted because is associate to 'conversation_id' conversation",
            $response->message
        );
    }
}
