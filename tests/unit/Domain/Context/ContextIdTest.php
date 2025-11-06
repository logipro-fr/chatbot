<?php

namespace Chatbot\Tests\Domain\Context;

use Chatbot\Domain\Model\Context\ContextId;
use PHPUnit\Framework\TestCase;

class ContextIdTest extends TestCase
{
    public function testIndentify(): void
    {
        $id1 = new ContextId();
        $id2 = new ContextId();
        $this->assertFalse($id1->equals($id2));
    }



    public function testIndentify2(): void
    {
        $id1 = new ContextId();
        $this->assertTrue($id1->equals($id1));
    }

    public function testValueId(): void
    {
        $id = new ContextId("cot_id");
        $this->assertEquals("cot_id", $id);
    }
}
