<?php

namespace Chatbot\Tests\Domain\Model\Exception;

use Chatbot\Domain\Model\Exception\BaseException;
use PHPUnit\Framework\TestCase;

class BaseExceptionTest extends TestCase
{
    protected string $exceptionClass;
    protected string $exceptionType;

    public function setUp(): void
    {
        $this->exceptionClass = BaseException::class;
        $this->exceptionType = "exception";
    }

    public function testCreate(): void
    {
        $error = new ($this->exceptionClass)("no");
        $this->assertInstanceOf(BaseException::class, $error);
        $this->assertEquals($this->exceptionType, $error->getType());
        $this->assertNull($error->getData());
        $this->assertEquals(0, $error->getCode());
    }
}
