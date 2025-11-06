<?php

namespace Chatbot\Tests\Application\Service\EditContext;

use Chatbot\Application\Service\EditContext\EditContext;
use Chatbot\Application\Service\EditContext\EditContextRequest;
use Chatbot\Application\Service\EditContext\EditContextResponse;
use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class EditContextTest extends TestCase
{
    public function testSomeoneEditAContext(): void
    {

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
        $service->execute(new EditContextRequest($newMessage, new ContextId($id)));
        $response = $service->getResponse();

        $this->assertInstanceOf(EditContextResponse::class, $response);
        $this->assertEquals($id, $response->contextId);
        $this->assertEquals(
            $newMessage,
            $repository->findById($response->contextId)->getContext()
        );
    }
}
