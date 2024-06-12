<?php

namespace Chatbot\Tests\Application\Service\ContextFactory;

use Chatbot\Application\Service\ContextFactory\ContextFactory;
use Chatbot\Domain\Model\Conversation\Context;
use PHPUnit\Framework\TestCase;

class ContextFactorytTest extends TestCase
{
    public function testContextFactory(): void
    {
        $this->assertInstanceOf(Context::class, (new ContextFactory())->create("english"));
        $context = "respond only with the prompt translated into english";
        $this->assertEquals($context, (new ContextFactory())->create("english")->context);
    }
}
