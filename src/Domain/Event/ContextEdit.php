<?php

namespace Chatbot\Domain\Event;

use Phariscope\Event\Psr14\Event;

class ContextEdit extends Event
{
    public function __construct(public readonly string $contextId, public readonly string $contextMessage)
    {
        parent::__construct();
    }
}
