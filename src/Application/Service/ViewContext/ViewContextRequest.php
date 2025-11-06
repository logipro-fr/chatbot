<?php

namespace Chatbot\Application\Service\ViewContext;

class ViewContextRequest
{
    public function __construct(
        public readonly string $id,
    ) {
    }
}
