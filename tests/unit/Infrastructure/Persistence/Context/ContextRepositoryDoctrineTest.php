<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context ;

use Chatbot\Infrastructure\Persistence\Context\ContextRepositoryDoctrine;
use DoctrineTestingTools\DoctrineRepositoryTesterTrait;

class ContextRepositoryDoctrineTest extends ContextRepositoryTestBase
{
    use DoctrineRepositoryTesterTrait;

    protected function initialize(): void
    {
        $this->initDoctrineTester();
        $this->contextRepository = new ContextRepositoryDoctrine($this->getEntityManager());
    }
}
