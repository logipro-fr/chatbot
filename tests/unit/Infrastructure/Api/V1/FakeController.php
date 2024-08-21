<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Chatbot\Infrastructure\Api\V1\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class FakeController extends AbstractController
{
    public const MY_CODE_SUCCESS = 201;

    public const MY_CODE_ERROR = 402;
    public const MY_MESSAGE_ERROR = "Unsuccessful response";

    public function publicWriteSuccessfulResponse(object $data): Response
    {
        return $this->writeSuccessfulResponse($data, self::MY_CODE_SUCCESS);
    }

    public function publicWriteUnsuccessfulResponse(): Response
    {
        return $this->writeUnsuccessfulResponse(new \Exception(self::MY_MESSAGE_ERROR, self::MY_CODE_ERROR));
    }
}
