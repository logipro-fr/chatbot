<?php

namespace Chatbot\Tests\Domain\Conversation;

use Chatbot\Domain\Event\ConversationCreated;
use Chatbot\Domain\Event\PairAdded;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Exceptions\LastPairDoesntExistException;
use Chatbot\Domain\Model\Conversation\Exceptions\PairOutOfRangeException;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\Prompt;
use DateTimeImmutable;
use Phariscope\Event\Tools\SpyListener;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable as SafeDateTimeImmutable;

class ConversationTest extends TestCase
{
    public function testConversationCreated(): void
    {
        $spy = new SpyListener();
        (new EventFacade())->subscribe($spy);


        $conversation = new Conversation(new ContextId());


        (new Eventfacade())->distribute();

        $event = $spy->domainEvent;

        $this->assertInstanceOf(ConversationCreated::class, $event);
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
        $this->assertEquals($conversation->getConversationId(), $event->conversationId);
    }

    public function testConversationId(): void
    {
        $conversation = new Conversation(new ContextId());
        $this->assertStringStartsWith("con_", $conversation->getConversationId());
    }

    public function testConversationIdInjected(): void
    {
        $conversation = new Conversation(new ContextId(), new ConversationId("absolumentcequejeveut"));
        $this->assertEquals("absolumentcequejeveut", $conversation->getConversationId());
    }

    public function testConversationHistory(): void
    {
        $conversation = new Conversation(new ContextId());


        $conversation->addPair(new Prompt("Bonjour"), new Answer("Bonjour, comment puis-je vous aider", 200));
        $conversation->addPair(new Prompt("racontes moi une blague"), new Answer("Je suis une blague", 200));

        $prompt = "Bonjour, comment puis-je vous aider";
        $this->assertEquals($prompt, $conversation->getPair(0)->getAnswer()->getMessage());
        $this->assertEquals("Bonjour", $conversation->getPair(0)->getPrompt()->getUserResquest());
        $this->assertEquals("Je suis une blague", $conversation->getPair(1)->getAnswer()->getMessage());
        $this->assertEquals("racontes moi une blague", $conversation->getPair(1)->getPrompt()->getUserResquest());
    }

    public function testConversationNbPair(): void
    {
        $conversation = new Conversation(new ContextId());

        $conversation->addPair(new Prompt("Bonjour"), new Answer("Bonjour", 200));
        $conversation->addPair(new Prompt("racontes moi une blague"), new Answer("Une blague", 200));

        $this->assertEquals(2, $conversation->countPair());
    }




    public function testPairAdded(): void
    {
        $spy = new SpyListener();
        (new EventFacade())->subscribe($spy);


        $conversation = new Conversation(new ContextId());

        $conversation->addPair(new Prompt("Bonjour"), new Answer("Bonjour", 200));
        $conversation->addPair(new Prompt("racontes moi une blague"), new Answer("Une blague", 200));

        (new Eventfacade())->distribute();

        $event = $spy->domainEvent;

        $this->assertInstanceOf(PairAdded::class, $event);
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
        $this->assertEquals($conversation->getConversationId(), $event->conversationId);
    }

    public function testConversationIsCreatedAt(): void
    {
        $creationTime = SafeDateTimeImmutable::createFromFormat('d/m/Y H:i:s', "12/03/2022 15:32:45");
        $conversation = new Conversation(new ContextId(), createdAt: $creationTime);
        $this->assertEquals($creationTime, $conversation->getCreatedAt());
    }

    public function testConversationTitle(): void
    {
        $creationTime = SafeDateTimeImmutable::createFromFormat('d/m/Y H:i:s', "12/03/2022 15:32:45");
        $conversation = new Conversation(new ContextId(), createdAt: $creationTime);
        $this->assertEquals("Conversation du 12/03/2022 15:32", $conversation->getTitle());
    }

    public function testPairOutOfRangeException(): void
    {
        $this->expectException(PairOutOfRangeException::class);
        $this->expectExceptionMessage("Index '1' out of range, pair cannot be found");
        $conversation = new Conversation(new ContextId());
        $conversation->getPair(1);
    }

    public function testLastPairDoesntExistException(): void
    {
        $this->expectException(LastPairDoesntExistException::class);
        $this->expectExceptionMessage("The last pair cannot be found");
        $conversation = new Conversation(new ContextId());
        $conversation->getLastPair();
    }
}
