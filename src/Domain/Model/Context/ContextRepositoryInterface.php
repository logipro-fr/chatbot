<?php

namespace Chatbot\Domain\Model\Context;

interface ContextRepositoryInterface
{
    public function add(Context $context): void;
    public function findById(ContextId $contextId): Context;
    public function removeContext(ContextId $context): void;
}
