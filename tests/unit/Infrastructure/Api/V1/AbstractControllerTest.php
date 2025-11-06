<?php

namespace Chatbot\Tests\Infrastructure\Api\V1;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class AbstractControllerTest extends TestCase
{
    use AssertResponseTrait;

    public function testWriteSuccessfulResponse(): void
    {
        $controller = new FakeController();
        $data = new FakeResponse("my_important_data");

        $response = $controller->publicWriteSuccessfulResponse($data);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $this->assertResponseSuccess(
            $response,
            (object)[
                "data" => "my_important_data"
            ]
        );
    }

    public function testWriteUnsuccessfulResponse(): void
    {
        $controller = new FakeController();

        $response = $controller->publicWriteUnsuccessfulResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertResponseFailure(
            $response,
            (new \ReflectionClass(\Exception::class))->getShortName()
        );
    }
}
