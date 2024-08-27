<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context;

use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;

class ContextRepositoryInMemoryTest extends ContextRepositoryTestBase
{
    protected function initialize(): void
    {
        $this->contextRepository = new ContextRepositoryInMemory();
    }
}
