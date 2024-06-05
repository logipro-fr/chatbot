<?php

namespace Chatbot\Tests\Domain;

use Chatbot\Application\Service\Request;
use Chatbot\Application\Service\Response;
use Chatbot\Domain\Model\Conversation\Prompt;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testRequest(): void
    {
        $request = new Request(new Prompt("Bonjour"));
        $response = new Response("Bonjour", 200);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Response::class, $response);
    }
}
