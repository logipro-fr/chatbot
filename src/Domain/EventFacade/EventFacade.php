<?php

namespace Chatbot\Domain\EventFacade;

use Phariscope\Event\EventDispatcher;
use Phariscope\Event\Psr14\Event;
use Phariscope\Event\Psr14\ListenerInterface;

class EventFacade
{
    public function subscribe(ListenerInterface $eventListener): void
    {
        EventDispatcher::instance()->subscribe($eventListener);
    }
    public function dispatch(Event $event): void
    {
        EventDispatcher::instance()->dispatch($event);
    }
    public function distribute(): void
    {
        EventDispatcher::instance()->distribute();
    }
}
