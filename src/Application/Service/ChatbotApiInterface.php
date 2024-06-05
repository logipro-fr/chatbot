<?php

namespace Chatbot\Application\Service;

use Chatbot\Application\Service\RequestInterface;
use Chatbot\Application\Service\ResponseInterface;

interface ChatbotApiInterface
{
    //cette fonction va servir a envoyer la request http a l'API
    public function request(RequestInterface $request): ResponseInterface;
}
