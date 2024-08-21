<?php

namespace Chatbot\Tests\Application\Service\MakeContext;

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

class MakeContextTest extends TestCase
{
    public function testSomeoneCreateAContext(): void
    {
        // arrange / Given

        $repository = new ContextRepositoryInMemory();
        $request = new MakeContextRequest(
            new ContextMessage("You're helpfull assistant")
        );
        $service = new MakeContext($repository);
        //act / When
        $service->execute($request);

        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(MakeContextResponse::class, $response);
        $this->assertIsString($response->contextId);
        $this->assertEquals(
            "You're helpfull assistant",
            $repository->findById(new ContextId($response->contextId))->getContext()->getMessage()
        );
    }
}
