<?php

namespace Chatbot\Tests\Application\Service\FindId;

use Chatbot\Application\Service\ContextFactory\ContextFactory;
use Chatbot\Application\Service\Exception\BadTypeNameException;
use Chatbot\Application\Service\FindId\FindId;
use Chatbot\Domain\Model\Context\Context;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Infrastructure\Persistence\Conversation\ConversationRepositoryInMemory;
use PHPUnit\Framework\TestCase;

class FindIdTest extends TestCase
{
    public function testFindContextIdWithConversationId(): void
    {
        $repo = new ConversationRepositoryInMemory();
        $repo->add(new Conversation(new PairArray(), new ContextId("base"), new ConversationId("con_zfnz5436z")));
        $service = new FindId($repo);
        $id = $service->find("conversations", "con_zfnz5436z");

        $this->assertEquals("base", $id);
    }

    public function testThrowBadName(): void
    {
        $repo = new ConversationRepositoryInMemory();
        $repo->add(new Conversation(new PairArray(), new ContextId("base"), new ConversationId("con_zfnz5436z")));

        $service = new FindId($repo);
        $this->expectException(BadTypeNameException::class);
        $id = $service->find("conv", "con_zfnz5436z");
    }
}
