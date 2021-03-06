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
    Serializer\Normalizer\HttpResource as ResourceNormalizer,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Headers,
    ProtocolVersion,
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

    public function setUp(): void
    {
        parent::setUp();

        $this->get = new Get(
            new Encoder\Json,
            new ResourceNormalizer,
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
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
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Get(
            new Encoder\Json,
            new ResourceNormalizer,
            Map::of('int', Gateway::class),
            $this->createMock(GetBuilder::class)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Get(
            new Encoder\Json,
            new ResourceNormalizer,
            Map::of('string', 'callable'),
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
        $request
            ->expects($this->any())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));
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
            ->willReturn($resource = HttpResource::of(
                $this->definition,
                new Property('uuid', 'foo'),
                new Property('url', 'example.com')
            ));
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($resource, $request, $this->definition, $identity)
            ->willReturn(Set::of(Header::class));

        $response = ($this->get)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', $response->reasonPhrase()->toString());
        $this->assertSame(
            '{"resource":{"uuid":"foo","url":"example.com"}}',
            $response->body()->toString(),
        );
    }
}
