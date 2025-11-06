<?php

namespace Chatbot\Tests\Domain\Conversation;

use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\Context;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;
use DateTimeImmutable;

class ContextTest extends TestCase
{
    public function testContextCreated(): void
    {
        $context = new Context(new ContextMessage("I'm a context"));

        $this->assertInstanceOf(Context::class, $context);
    }

    public function testContextId(): void
    {
        $context = new Context(new ContextMessage("I'm a context"));
        $this->assertStringStartsWith("cot_", $context->getContextId());
    }

    public function testContextIdInjected(): void
    {
        $context = new Context(new ContextMessage(""), new ContextId("absolumentcequejeveut"));
        $this->assertEquals("absolumentcequejeveut", $context->getContextId());
    }

    public function testContextIsCreatedAt(): void
    {
        $creationTime = SafeDateTimeImmutable::createFromFormat('d/m/Y H:i:s', "12/03/2022 15:32:45");
        $context = new Context(new ContextMessage(""), createdAt: $creationTime);
        $this->assertEquals($creationTime, $context->getCreatedAt());
    }

    public function testContextMessage(): void
    {
        $context = new Context(new ContextMessage("I'm a context message"));
        $this->assertEquals("I'm a context message", $context->getContext()->getMessage());
    }
}
