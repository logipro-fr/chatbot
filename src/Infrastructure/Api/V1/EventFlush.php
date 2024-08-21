<?php

namespace Chatbot\Infrastructure\Api\V1;

use Chatbot\Domain\EventFacade\EventFacade;
use Doctrine\ORM\EntityManagerInterface;

class EventFlush
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventFacade $eventFacade = new EventFacade()
    ) {
    }

    public function flushAndDistribute(): void
    {
        $this->em->flush();
        try {
            $this->eventFacade->distribute();
        } catch (\Exception $e) {
        }
        $this->em->flush();
    }
}
