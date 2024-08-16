<?php

namespace Chatbot\Tests\Application\Service\EditContext;

use Chatbot\Application\Service\EditContext\EditContext;
use Chatbot\Application\Service\EditContext\EditContextRequest;
use Chatbot\Application\Service\EditContext\EditContextResponse;
use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\MakeContext\MakeContextResponse;
use Chatbot\Application\Service\MakeConversation\MakeConversationResponse;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class EditContextTest extends TestCase
{
    public function testSomeoneEditAContext(): void
    {
        // arrange / Given

        $repository = new ContextRepositoryInMemory();
        $request = new MakeContextRequest(
            new ContextMessage("You're helpfull assistant")
        );
        $service = new MakeContext($repository);

        $service->execute($request);
        $response = $service->getResponse();
        $id = $response->contextId;
        $newMessage = new ContextMessage("I'm a new context message");
        $service = new EditContext($repository);
        $service->execute(new EditContextRequest($newMessage, $id));
        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(EditContextResponse::class, $response);
        $this->assertEquals($id, $response->contextId);
        $this->assertEquals(
            $newMessage,
            $repository->findById($response->contextId)->getContext()
        );
    }
}
