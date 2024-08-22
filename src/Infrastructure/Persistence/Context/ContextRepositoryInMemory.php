<?php

namespace Chatbot\Infrastructure\Persistence\Context;

use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Context\ContextMessage;
use Chatbot\Domain\Model\Context\ContextRepositoryInterface;
use Chatbot\Infrastructure\Exception\ConversationNotFoundException;

class ContextRepositoryInMemory implements ContextRepositoryInterface
{
     /**
     * @var array<Context>
     */
    private array $contexts;

    public function __construct()
    {
        $context = new Context(new ContextMessage("You're helpfull assistant"), new ContextId("base"));
        $this->add($context);
        $context = new Context(new ContextMessage("english"), new ContextId("inEnglish"));
        $this->add($context);
    }
    public function add(Context $context): void
    {
        $this->contexts[$context->getId()->__toString()] = $context;
    }


    public function findById(ContextId $contextId): Context
    {
        if (!isset($this->contexts[$contextId->__toString()])) {
            throw new ConversationNotFoundException("");
        }
        return $this->contexts[$contextId->__toString()] ;
    }

    public function removeContext(ContextId $contextId): void
    {
        unset($this->contexts[$contextId->__toString()]);
    }
}
