<?php

namespace Chatbot\Tests\Domain\EventFacade;

use Chatbot\Domain\EventFacade\EventFacade;
use Phariscope\Event\EventDispatcher;
use Phariscope\Event\Tools\SpyListener;
use PHPUnit\Framework\TestCase;

class EventFacadetest extends TestCase
{
    public function testSubscribe(): void
    {
        $listener = new SpyListener();
        $facade = new EventFacade();

        $facade->subscribe($listener);

        $this->assertTrue(EventDispatcher::instance()->hasSubscriber($listener));
    }

    public function testDispatchEvent(): void
    {
        $event = new EventFake("unId");
        $spy = new SpyListener();
        $facade = new EventFacade();
        $facade->subscribe($spy);

        $facade->dispatch($event);
        $facade->distribute();
        $this->assertInstanceOf(EventFake::class, $spy->domainEvent);
    }
}
