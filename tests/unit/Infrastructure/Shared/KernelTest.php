<?php

namespace Chatbot\Tests\Infrastructure\Shared\Symfony;

use PHPUnit\Framework\TestCase;
use Chatbot\Infrastructure\Shared\Symfony\Kernel;


class KernelTest extends TestCase
{
    public function testConstruct(): void
    {

        $kernel = new Kernel("test", true);
        $this->assertInstanceOf(Kernel::class, $kernel);
        $this->assertTrue($kernel->isDebug());
    }
}
