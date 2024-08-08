<?php

namespace Chatbot\Tests\Application\Service\MakeContext;

use Chatbot\Application\Service\ChangeContextConversation\ChangeContextConversation;
use Chatbot\Application\Service\ChangeContextConversation\ChangeContextConversationRequest;
use Chatbot\Application\Service\ChangeContextConversation\ChangeContextConversationResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class ChangeContextConversationTest extends TestCase
{
    public function testChangeContextConversation(): void
    {
        // arrange / Given

        $repository = new ContextRepositoryInMemory();
        $convrepository = new ConversationRepositoryInMemory();
        $convrepository->add(new Conversation(new PairArray(),new ContextId("Base"),new ConversationId("conversation_base")));
        $request = new ChangeContextConversationRequest(
            new ContextId("New_Id"),
            new ConversationId("conversation_base")
        );
        $service = new ChangeContextConversation($convrepository);
        
        $service->execute($request);
        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(ChangeContextConversationResponse::class, $response);
        $this->assertEquals(
            "New_Id",
            $convrepository->findById(new ConversationId("conversation_base"))->getContext()
        );
    }
}
