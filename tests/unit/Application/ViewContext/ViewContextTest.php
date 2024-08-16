<?php

namespace Chatbot\Tests\Application\Service\ViewContext;

use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\ViewContext\ViewContext;
use Chatbot\Application\Service\ViewContext\ViewContextRequest;
use Chatbot\Application\Service\ViewContext\ViewContextResponse;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class ViewContextTest extends TestCase
{
    public function testFindContextWithContextId(): void
    {
        // arrange / Given

        $repository = new ContextRepositoryInMemory();
        $convrepository = new ConversationRepositoryInMemory();
        $request = new MakeContextRequest(
            new ContextMessage("You're helpfull assistant")
        );
        $service = new MakeContext($repository);
        $service->execute($request);
        $id = $service->getResponse()->contextId->__toString();

        $request = new ViewContextRequest($id, "contexts");
        $service = new ViewContext($repository, $convrepository);

        $service->execute($request);
        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(ViewContextResponse::class, $response);
        $this->assertInstanceOf(ContextMessage::class, $response->contextMessage);
        $this->assertEquals("You're helpfull assistant", $response->contextMessage->getMessage());
    }
}
