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
    Headers,
    Header\Accept,
    Header\AcceptValue,
    ProtocolVersion,
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
        $request
            ->expects($this->any())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));

        $response = ($this->options)($request, $this->definition);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', $response->reasonPhrase()->toString());
        $this->assertSame(
            'Content-Type: application/json',
            $response->headers()->get('content-type')->toString(),
        );
        $this->assertSame(
            '{"identity":"uuid","properties":{"uuid":{"type":"string","access":["READ"],"variants":[],"optional":false},"url":{"type":"string","access":["READ","CREATE","UPDATE"],"variants":[],"optional":false}},"metas":[],"rangeable":false,"linkable_to":[]}',
            $response->body()->toString(),
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
