<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Get,
    Controller,
    Identity,
    Gateway,
    Response\HeaderBuilder\GetBuilder,
    HttpResource\HttpResource,
    HttpResource\Property,
    ResourceAccessor,
    Serializer\Encoder,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Headers\Headers,
};
use Innmind\Immutable\{
    Map,
    Set,
};

class GetTest extends AbstractTestCase
{
    private $get;
    private $gateway;
    private $headerBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->get = new Get(
            new Encoder\Json,
            $this->serializer,
            (new Map('string', Gateway::class))->put(
                'foo',
                $this->gateway = $this->createMock(Gateway::class)
            ),
            $this->headerBuilder = $this->createMock(GetBuilder::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            $this->get
        );
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Get(
            new Encoder\Json,
            $this->serializer,
            new Map('int', Gateway::class),
            $this->createMock(GetBuilder::class)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Get(
            new Encoder\Json,
            $this->serializer,
            new Map('string', 'callable'),
            $this->createMock(GetBuilder::class)
        );
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
        $identity = $this->createMock(Identity::class);
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceAccessor')
            ->willReturn($accessor = $this->createMock(ResourceAccessor::class));
        $accessor
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->definition, $identity)
            ->willReturn($resource = new HttpResource(
                $this->definition,
                (new Map('string', Property::class))
                    ->put('uuid', new Property('uuid', 'foo'))
                    ->put('url', new Property('url', 'example.com'))
            ));
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($resource, $request, $this->definition, $identity)
            ->willReturn(new Set(Header::class));

        $response = ($this->get)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', (string) $response->reasonPhrase());
        $this->assertSame(
            '{"resource":{"uuid":"foo","url":"example.com"}}',
            (string) $response->body()
        );
    }
}
