<?php

namespace Chatbot\Tests\Application\Service\DeleteAssistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantRequest;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class DeleteAssistantRequestTest extends TestCase
{
    public function testConstructor(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $request = new DeleteAssistantRequest($assistantId);

        $this->assertEquals($assistantId, $request->assistantId);
    }
}
