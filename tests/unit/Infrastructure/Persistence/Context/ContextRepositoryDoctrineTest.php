<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Context ;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextMessage;
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

    public function testFindByMessageWithExistingMessage(): void
    {
        $message = "Test message for findByMessage";
        $context = new Context(new ContextMessage($message));
        $this->contextRepository->add($context);

        $this->getEntityManager()->flush();

        $foundContext = $this->contextRepository->findByMessage($message);

        $this->assertNotNull($foundContext);
        $this->assertEquals($message, $foundContext->getContext()->getMessage());
    }
}
