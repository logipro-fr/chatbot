<?php 

namespace Chatbot\Tests\Application\Service\UpdateAssistant;

use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class UpdateAssistantResponseTest extends TestCase
{
    public function testConstructor():void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $newName = 'New Assistant Name';
        $newInstructions = 'These are the new instructions for the assistant.';

        $response = new UpdateAssistantResponse($assistantId, $newName, $newInstructions);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($newName, $response->newName);
        $this->assertEquals($newInstructions, $response->newInstructions);
    }

    public function testConstructorWithNullValues():void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $newName = 'New Assistant Name';

        $response = new UpdateAssistantResponse($assistantId, $newName, null);
        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($newName, $response->newName);
        $this->assertNull($response->newInstructions);
    }


}