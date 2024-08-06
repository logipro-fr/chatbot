<?php

namespace Chatbot\Infrastructure\Persistence\Context;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;

class ContextRepositoryInMemory implements ContextRepositoryInterface
{
     /**
     * @var array<Context>
     */
    private array $contexts;

    public function add(Context $context): void
    {
        $this->contexts[$context->getId()->__toString()] = $context;
    }


    public function findById(ContextId $contextId): Context
    {

        return $this->contexts[$contextId->__toString()];
    }
}
