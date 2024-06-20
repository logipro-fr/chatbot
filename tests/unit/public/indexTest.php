<?php

namespace Chatbot\Tests\public ;

use Chatbot\Infrastructure\Shared\Symfony\Kernel;
use Closure;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testNoError(): void
    {
        $sut = include_once(getcwd() . '/src/public/index.php');
        $this->assertInstanceOf(Closure::class, $sut);
        $this->assertInstanceOf(Kernel::class, $sut(['APP_ENV' => 'test', 'APP_DEBUG' => false]));
    }
}
