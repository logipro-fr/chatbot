<?php 

namespace Chatbot\Tests\Domain;

use Chatbot\Domain\Model\Conversation\Answer;
use Chatbot\Domain\Model\Conversation\Pair;
use Chatbot\Domain\Model\Conversation\PairArray;
use Chatbot\Domain\Model\Conversation\Prompt;
use PHPUnit\Framework\TestCase;

class PairArrayTest extends TestCase{
    public function testAdd(){
       $pairArray =  new PairArray();
       $pair0 = new Pair(new Prompt("Bonjour"), new Answer("Réponse Bonjour",200));
       $pair1 = new Pair(new Prompt("Bonjour1"), new Answer("Réponse Bonjour1",200));
       $pair2 = new Pair(new Prompt("Bonjour2"), new Answer("Réponse Bonjour2",200));

       $pairArray -> add($pair0);
       $pairArray -> add($pair1);
       $pairArray -> add($pair2);

       $this->assertEquals($pair0, $pairArray->getPair(0));
       $this->assertEquals($pair1, $pairArray->getPair(1));
       $this->assertEquals($pair2, $pairArray->getPair(2)); 
       $this->assertEquals(3, $pairArray->getNb());       
    }
}