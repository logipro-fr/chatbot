<?php

namespace Chatbot\Infrastructure\Api\V1;

use Symfony\Component\HttpFoundation\Response;

use function Safe\json_encode;

abstract class AbstractController
{
    public const ERROR_CODE_SUCCESS = 200;
    public const ERROR_CODE_ERROR = 500;

    protected function writeSuccessfulResponse(object $data, int $httpStatusCode = self::ERROR_CODE_SUCCESS): Response
    {

        return new Response(
            json_encode([
                "success" => true,
                "data" => $data
            ]),
            $httpStatusCode,
            ["Content-Type" => "application/json"]
        );
    }

    protected function writeUnsuccessfulResponse(\Exception $e, int $code = self::ERROR_CODE_ERROR): Response
    {
        $exceptionClassName = (new \ReflectionClass($e))->getShortName();
        $errorMessage = $e->getMessage();
        return new Response(
            json_encode([
                "success" => false,
                "error" => $exceptionClassName,
                "error_message" => $errorMessage,
            ]),
            $code,
            ["Content-Type" => "application/json"]
        );
    }
}
