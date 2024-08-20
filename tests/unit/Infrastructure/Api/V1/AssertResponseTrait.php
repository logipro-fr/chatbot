<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use Symfony\Component\HttpFoundation\Response;

use function Safe\json_decode;

trait AssertResponseTrait
{
    public function assertResponseFailure(Response $response, string $raisedExceptionClassName): void
    {
        $this->assertTrue($response->headers->has("Content-Type"));
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));

        /** @var string $content */
        $content = $response->getContent();
        /** @var \stdClass $responseObject */
        $responseObject = json_decode($content);
        $this->assertFalse($responseObject->success);
        $this->assertEquals($raisedExceptionClassName, $responseObject->error);
        $this->assertArrayNotHasKey("data", (array) $responseObject);
    }

    public function assertResponseSuccess(Response $response, object $data): void
    {
        $this->assertTrue($response->headers->has("Content-Type"));
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));

        /** @var string $content */
        $content = $response->getContent();
        /** @var \stdClass $responseObject */
        $responseObject = json_decode($content);
        $this->assertTrue($responseObject->success);
        $this->assertEquals($data, $responseObject->data);
        $this->assertArrayNotHasKey("error", (array) $responseObject);
    }

    public function assertOnlySuccess(Response $response): void
    {
        $this->assertTrue($response->headers->has("Content-Type"));
        $this->assertEquals("application/json", $response->headers->get("Content-Type"));

        /** @var string $content */
        $content = $response->getContent();
        /** @var \stdClass $responseObject */
        $responseObject = json_decode($content);
        $this->assertTrue($responseObject->success);
        $this->assertArrayNotHasKey("error", (array) $responseObject);
    }
}
