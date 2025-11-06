<?php

namespace Chatbot\Application\Service;

use Chatbot\Application\Service\RequestInterface;
use Chatbot\Application\Service\ResponseInterface;

interface ChatbotApiInterface
{
    public function request(RequestInterface $request): ResponseInterface;
}
