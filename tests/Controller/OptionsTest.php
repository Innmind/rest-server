<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Options,
    Controller,
    Identity\Identity,
    Serializer\Encoder,
    Serializer\Normalizer\Definition,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Headers\Headers,
    Header\Accept,
    Header\AcceptValue,
};

class OptionsTest extends AbstractTestCase
{
    private $options;

    public function setUp(): void
    {
        parent::setUp();

        $this->options = new Options(
            new Encoder\Json,
            $this->format,
            new Definition
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Controller::class, $this->options);
    }

    public function testInvokation()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new Accept(
                    new AcceptValue('application', 'json')
                )
            ));

        $response = ($this->options)($request, $this->definition);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame(
            'Content-Type: application/json',
            (string) $response->headers()->get('content-type')
        );
        $this->assertSame(
            '{"identity":"uuid","properties":{"uuid":{"type":"string","access":["READ"],"variants":[],"optional":false},"url":{"type":"string","access":["READ","CREATE","UPDATE"],"variants":[],"optional":false}},"metas":[],"rangeable":false,"linkable_to":[]}',
            (string) $response->body()
        );
    }

    public function testThrowWhenProvidingUnwantedIdentity()
    {
        $this->expectException(LogicException::class);

        ($this->options)(
            $this->createMock(ServerRequest::class),
            $this->definition,
            new Identity(42)
        );
    }
}
