<?php

namespace Chatbot\Tests\Application\Service\ViewContext;

use Chatbot\Application\Service\MakeContext\MakeContext;
use Chatbot\Application\Service\MakeContext\MakeContextRequest;
use Chatbot\Application\Service\ViewContext\ViewContext;
use Chatbot\Application\Service\ViewContext\ViewContextRequest;
use Chatbot\Application\Service\ViewContext\ViewContextResponse;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;

class ViewContextTest extends TestCase
{
    public function testFindContextWithContextId(): void
    {
        // arrange / Given

        $repository = new ContextRepositoryInMemory();
        $request = new MakeContextRequest(
            new ContextMessage("You're helpfull assistant")
        );
        $service = new MakeContext($repository);
        $service->execute($request);
        $id = $service->getResponse()->contextId;

        $request = new ViewContextRequest($id);
        $service = new ViewContext($repository);

        $service->execute($request);
        $response = $service->getResponse();

        //assert / Then
        $this->assertInstanceOf(ViewContextResponse::class, $response);
        $this->assertIsString($response->contextMessage);
        $this->assertEquals("You're helpfull assistant", $response->contextMessage);
    }
}
