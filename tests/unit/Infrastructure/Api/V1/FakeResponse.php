<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

class FakeResponse
{
    public function __construct(
        public readonly string $data
    ) {
    }
}
