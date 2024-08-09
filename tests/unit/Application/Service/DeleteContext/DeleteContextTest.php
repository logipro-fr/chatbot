<?php

namespace Chatbot\Tests\Application\Service\EditContext;

use Chatbot\Application\Service\DeleteContext\DeleteContext;
use Chatbot\Application\Service\DeleteContext\DeleteContextRequest;
use Chatbot\Application\Service\DeleteContext\DeleteContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Infrastructure\Exception\ContextAssociatedConversationException;
use Chatbot\Infrastructure\Exception\NoIdException;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryDoctrine;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class DeleteContextTest extends TestCase

{
    public function testSomeoneDeleteAContext(): void
    {
        // arrange / Given

        $conv = new ConversationRepositoryInMemory();
        $conv->add(new Conversation(new PairArray(), new ContextId("english"), new ConversationId("conversation_id")));
        $repository = new ContextRepositoryInMemory();
        $request = new DeleteContextRequest(
            new ContextId("base")
        );
        $service = new DeleteContext($repository, $conv);
        
        
        $service->execute($request);
        $response = $service->getResponse();
        
        //assert / Then
        $this->assertInstanceOf(DeleteContextResponse::class, $response);
    }

    public function testSomeoneDeleteAContextAssociatedConversation(): void
    {
        // arrange / Given

        $conv = new ConversationRepositoryInMemory();
        $conv->add(new Conversation(new PairArray(), new ContextId("base"), new ConversationId("conversation_id")));
        $repository = new ContextRepositoryInMemory();
        $request = new DeleteContextRequest(
            new ContextId("base")
        );
        $service = new DeleteContext($repository, $conv);
        //assert / Then
      
        $this->expectException(ContextAssociatedConversationException::class);

        $service->execute($request);
        $response = $service->getResponse();
    }
}