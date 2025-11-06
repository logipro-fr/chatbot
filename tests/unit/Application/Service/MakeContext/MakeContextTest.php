<?php

namespace Chatbot\Tests\Application\Service\MakeContext;

use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\MakeContext\MakeContextResponse;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class MakeContextTest extends TestCase
{
    public function testSomeoneCreateAContext(): void
    {

        $repository = new ContextRepositoryInMemory();
        $request = new MakeContextRequest(
            new ContextMessage("You're helpfull assistant")
        );
        $service = new MakeContext($repository);
        $service->execute($request);

        $response = $service->getResponse();

        $this->assertInstanceOf(MakeContextResponse::class, $response);
        $this->assertEquals(
            "You're helpfull assistant",
            $repository->findById(new ContextId($response->contextId))->getContext()->getMessage()
        );
    }
}
