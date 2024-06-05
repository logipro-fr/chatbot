<?php

namespace Chatbot\Tests\Infrastructure\Persistence\Conversation;

use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;

class ConversationRepositoryInMemoryTest extends ConversationRepositoryTestBase
{
   protected function initialize(): void 
   {
    $this->repository = new ConversationRepositoryInMemory;
   }
}
