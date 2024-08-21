<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Infrastructure\Api\V1\EventFlush;
use Chatbot\Tests\Domain\EventFacade\EventFake;
use Doctrine\ORM\EntityManager;
use Phariscope\Event\Tools\SpyListener;
use PHPUnit\Framework\TestCase;

class FlushEventTest extends TestCase
{
    public function testDistributeEvent(): void
    {
        $event = new EventFake("unId");
        $spy = new SpyListener();
        $facade = new EventFacade();
        $facade->subscribe($spy);
        $facade->dispatch($event);
        $em = $this->createMock(EntityManager::class);

        /** @var EntityManager $em */
        $sut = new EventFlush($em);
        $sut->flushAndDistribute();

        $this->assertInstanceOf(EventFake::class, $spy->domainEvent);
    }

    public function testDoctrineFlush(): void
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->atLeastOnce())->method('flush');
        /** @var EntityManager $em */
        $sut = new EventFlush($em);
        $sut->flushAndDistribute();
    }

    public function testFlushISDoneEvenIfDistributeFail(): void
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->atLeastOnce())->method('flush');

        /** @var EntityManager $em */

        $facade = $this->createMock(EventFacade::class);
        $facade->method('distribute')->willReturnCallback(function () {
            throw new \Exception();
        });

        $sut = new EventFlush($em, $facade);
        $sut->flushAndDistribute();
    }

    public function testGoodOrderFlusDistributeFlush(): void
    {
        $callOrder = [];

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->any())->method('flush')->willReturnCallback(function () use (&$callOrder) {
            $callOrder[] = 'flush';
        });

        /** @var EntityManager $em */

        $facade = $this->createMock(EventFacade::class);
        $facade->method('distribute')->willReturnCallback(function () use (&$callOrder) {
            $callOrder[] = 'distribute';
        });

        $sut = new EventFlush($em, $facade);
        $sut->flushAndDistribute();
        $this->assertEquals(['flush', 'distribute', 'flush'], $callOrder);
    }
}
