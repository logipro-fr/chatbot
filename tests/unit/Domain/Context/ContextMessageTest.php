<?php

namespace Chatbot\Tests\Domain\Context;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use PHPUnit\Framework\TestCase;

class ContextMessageTest extends TestCase{
    public function testCountToken1(): void
    {
        $message = new ContextMessage("Bonjour");
        $this->assertEquals(1, $message->countToken());
    }
}
