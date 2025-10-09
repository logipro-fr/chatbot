<?php

namespace Chatbot\Tests\Application\Service\DeleteAssistant;

use Chatbot\Application\Service\DeleteAssistant\DeleteAssistantResponse;
use Chatbot\Domain\Model\Assistant\AssistantId;
use PHPUnit\Framework\TestCase;

class DeleteAssistantResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $assistantId = new AssistantId('test-assistant-id');
        $externalAssistantId = 'external-assistant-id';

        $response = new DeleteAssistantResponse($assistantId, $externalAssistantId);

        $this->assertEquals($assistantId, $response->assistantId);
        $this->assertEquals($externalAssistantId, $response->externalAssistantId);
    }
}
