<?php

namespace Chatbot\Tests\Domain\Conversation;

use Chatbot\Domain\Event\ConversationCreated;
use Chatbot\Domain\Event\PairAdded;
use Chatbot\Domain\EventFacade\EventFacade;
use Chatbot\Domain\Model\Context\ContextId;
use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Conversation;
use Chatbot\Domain\Model\Conversation\ConversationId;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\PairArray;
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


        $conversation = new Conversation(new PairArray(), new ContextId());


        (new Eventfacade())->distribute();

        $event = $spy->domainEvent;

        $this->assertInstanceOf(ConversationCreated::class, $event);
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
        $this->assertEquals($conversation->getId(), $event->conversationId);
    }

    public function testConversationId(): void
    {
        //arrange /Given
        //act /When
        $conversation = new Conversation(new PairArray(), new ContextId());
        //assert /then
        $this->assertStringStartsWith("con_", $conversation->getId());
    }

    public function testConversationIdInjected(): void
    {
        //arrange /Given
        //act /When
        $conversation = new Conversation(new PairArray(), new ContextId(),new ConversationId("absolumentcequejeveut"));
        //assert /then
        $this->assertEquals("absolumentcequejeveut", $conversation->getId());
    }

    public function testConversationHistory(): void
    {
        //arrange /Given
        $conversation = new Conversation(new PairArray(), new ContextId());
        $pair1 = new Pair(new Prompt("Bonjour"), new Answer("Bonjour, comment puis-je vous aider", 200));
        $pair2 = new Pair(new Prompt("racontes moi une blague"), new Answer("Je suis une blague", 200));

        //act /When

        $conversation->addPair(new Prompt("Bonjour"), new Answer("Bonjour, comment puis-je vous aider", 200));
        $conversation->addPair(new Prompt("racontes moi une blague"), new Answer("Je suis une blague", 200));

        //assert /then
        $this->assertEquals($pair1, $conversation->getPair(0));
        $prompt = "Bonjour, comment puis-je vous aider";
        $this->assertEquals($prompt, $conversation->getPair(0)->getAnswer()->getMessage());
        $this->assertEquals("Bonjour", $conversation->getPair(0)->getPrompt()->getUserResquest());
        $this->assertEquals($pair2, $conversation->getPair(1));
        $this->assertEquals("Je suis une blague", $conversation->getPair(1)->getAnswer()->getMessage());
        $this->assertEquals("racontes moi une blague", $conversation->getPair(1)->getPrompt()->getUserResquest());
    }

    public function testConversationNbPair(): void
    {
        $conversation = new Conversation(new PairArray(), new ContextId());

        //act /When
        $conversation->addPair(new Prompt("Bonjour"), new Answer("Bonjour", 200));
        $conversation->addPair(new Prompt("racontes moi une blague"), new Answer("Une blague", 200));

        //assert /then
        $this->assertEquals(2, $conversation->getNbPair());
    }




    public function testPairAdded(): void
    {
        $spy = new SpyListener();
        (new EventFacade())->subscribe($spy);


        $conversation = new Conversation(new PairArray(), new ContextId());

        //act /When
        $conversation->addPair(new Prompt("Bonjour"), new Answer("Bonjour", 200));
        $conversation->addPair(new Prompt("racontes moi une blague"), new Answer("Une blague", 200));

        (new Eventfacade())->distribute();

        $event = $spy->domainEvent;

        $this->assertInstanceOf(PairAdded::class, $event);
        $this->assertInstanceOf(DateTimeImmutable::class, $event->occurredOn());
        $this->assertEquals($conversation->getId(), $event->conversationId);
    }

    public function testConversationIsCreatedAt(): void
    {
        $creationTime = SafeDateTimeImmutable::createFromFormat('d/m/Y H:i:s', "12/03/2022 15:32:45");
        $conversation = new Conversation(new PairArray(), new ContextId() ,createdAt: $creationTime);
        $this->assertEquals($creationTime, $conversation->getCreatedAt());
    }
}
