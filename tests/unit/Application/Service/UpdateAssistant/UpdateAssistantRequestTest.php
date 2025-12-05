<?php 

namespace Chatbot\Tests\Application\Service\UpdateAssistant;

use Chatbot\Application\Service\UpdateAssistant\UpdateAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class UpdateAssistantRequestTest extends TestCase
{
    
    public function testConstructor():void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $newName = 'Updated Assistant Name';
        $newInstructions = 'Updated description for the assistant.';

        $request = new UpdateAssistantRequest($assistantId, $newName, $newInstructions);

        $this->assertEquals($assistantId, $request->assistantId);
        $this->assertEquals($newName, $request->newName);
        $this->assertEquals($newInstructions, $request->newInstructions);
    }

    public function testConstructorWithNullValues():void
    {
        $assistantId = new AssistantId('test-assistant-id');

        $request = new UpdateAssistantRequest($assistantId, null, null);

        $this->assertEquals($assistantId, $request->assistantId);
        $this->assertNull($request->newName);
        $this->assertNull($request->newInstructions);
    }
}