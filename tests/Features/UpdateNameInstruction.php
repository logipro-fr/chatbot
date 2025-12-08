<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use Chatbot\Domain\Model\Assistant\Assistant;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\Assert;

class UpdateNameInstructionContext implements Context
{
    private Assistant $assistant;
    private AssistantId $assistantId;

     #[Given('un chatbot nommé :name avec les instructions :instructions existe')]
    public function unChatbotNommeAvecLesInstructionsExiste(string $name, string $instructions): void
    {
        $this->assistantId = new AssistantId();
        $externalAssistantId = "asst_123456";

        $this->assistant = new Assistant($this->assistantId, $name, $instructions, $externalAssistantId);
    }

    #[When('l\'utilisateur met à jour le nom du chatbot en :name et les instructions en :instructions')]
    public function lutilisateurMetAJourLeNomDuChatbotEnEtLesInstructionsEn(string $name, string $instructions): void
    {
        $this->assistant->setName($name);
        $this->assistant->setInstructions($instructions);
    }

    #[Then('le chatbot doit avoir le nom :name')]
    public function leChatbotDoitAvoirLeNom(string $name): void
    {
        Assert::assertSame($name, $this->assistant->getName());
    }

    #[Then('le chatbot doit avoir les instructions :instructions')]
    public function leChatbotDoitAvoirLesInstructions(string $instructions): void
    {
        Assert::assertEquals($instructions, $this->assistant->getInstructions());
    }

    #[When('l\'utilisateur met à jour le nom du chatbot en :name')]
    public function lutilisateurMetAJourLeNomDuChatbotEn(string $name): void
    {
        $this->assistant->setName($name);
    }

    #[When('l\'utilisateur met à jour les instructions du chatbot en :instructions')]
    public function lutilisateurMetAJourLesInstructionsDuChatbotEn(string $instructions): void
    {
        $this->assistant->setInstructions($instructions);
    }
}
