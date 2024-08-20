<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context;

use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryInMemory;

class contextRepositoryInMemoryTest extends contextRepositoryTestBase
{
    protected function initialize(): void
    {
        $this->repository = new ContextRepositoryInMemory();
    }
}
