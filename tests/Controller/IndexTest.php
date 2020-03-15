<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Index,
    Controller,
    Gateway,
    Response\HeaderBuilder\ListBuilder,
    RangeExtractor\Extractor,
    SpecificationBuilder\Builder,
    ResourceListAccessor,
    Identity,
    Request\Range,
    Serializer\Encoder,
    Serializer\Normalizer\Identities,
    Exception\RangeNotFound,
    Exception\NoFilterFound,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    ProtocolVersion,
    Exception\Http\RangeNotSatisfiable,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use Innmind\Specification\Specification;

class IndexTest extends AbstractTestCase
{
    private $index;
    private $gateway;
    private $headerBuilder;
    private $rangeExtractor;
    private $builder;

    public function setUp(): void
    {
        parent::setUp();

        $this->index = new Index(
            new Encoder\Json,
            new Identities,
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
            $this->headerBuilder = $this->createMock(ListBuilder::class),
            $this->rangeExtractor = $this->createMock(Extractor::class),
            $this->builder = $this->createMock(Builder::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Controller::class, $this->index);
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Index(
            new Encoder\Json,
            new Identities,
            Map::of('int', Gateway::class),
            $this->createMock(ListBuilder::class),
            $this->createMock(Extractor::class),
            $this->createMock(Builder::class)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Index(
            new Encoder\Json,
            new Identities,
            Map::of('string', 'callable'),
            $this->createMock(ListBuilder::class),
            $this->createMock(Extractor::class),
            $this->createMock(Builder::class)
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
        $this
            ->rangeExtractor
            ->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($range = new Range(0, 42));
        $this
            ->builder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition)
            ->willReturn($spec = $this->createMock(Specification::class));
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceListAccessor')
            ->willReturn($accessor = $this->createMock(ResourceListAccessor::class));
        $accessor
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->definition, $spec, $range)
            ->willReturn($identities = Set::of(
                Identity::class,
                new Identity\Identity('uuid1'),
                new Identity\Identity('uuid2')
            ));
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($identities, $request, $this->definition, $spec, $range)
            ->willReturn(Set::of(Header::class));

        $response = ($this->index)($request, $this->definition);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(206, $response->statusCode()->value());
        $this->assertSame('Partial Content', $response->reasonPhrase()->toString());
        $this->assertSame(
            '{"identities":["uuid1","uuid2"]}',
            $response->body()->toString(),
        );
    }

    public function testInvokationWithoutRange()
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
        $this
            ->rangeExtractor
            ->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->will($this->throwException(new RangeNotFound));
        $this
            ->builder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition)
            ->willReturn($spec = $this->createMock(Specification::class));
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceListAccessor')
            ->willReturn($accessor = $this->createMock(ResourceListAccessor::class));
        $accessor
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->definition, $spec, null)
            ->willReturn($identities = Set::of(
                Identity::class,
                new Identity\Identity('uuid1'),
                new Identity\Identity('uuid2')
            ));
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($identities, $request, $this->definition, $spec, null)
            ->willReturn(Set::of(Header::class));

        $response = ($this->index)($request, $this->definition);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->statusCode()->value());
        $this->assertSame('OK', $response->reasonPhrase()->toString());
        $this->assertSame(
            '{"identities":["uuid1","uuid2"]}',
            $response->body()->toString(),
        );
    }

    public function testThrowWhenNoResourcesFoundInRange()
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
        $this
            ->rangeExtractor
            ->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($range = new Range(0, 42));
        $this
            ->builder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition)
            ->willReturn($spec = $this->createMock(Specification::class));
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceListAccessor')
            ->willReturn($accessor = $this->createMock(ResourceListAccessor::class));
        $accessor
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->definition, $spec, $range)
            ->willReturn(Set::of(Identity::class));
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(RangeNotSatisfiable::class);

        ($this->index)($request, $this->definition);
    }

    public function testInvokationWithoutSpecification()
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
        $this
            ->rangeExtractor
            ->expects($this->once())
            ->method('__invoke')
            ->with($request)
            ->willReturn($range = new Range(0, 42));
        $this
            ->builder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition)
            ->will($this->throwException(new NoFilterFound));
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceListAccessor')
            ->willReturn($accessor = $this->createMock(ResourceListAccessor::class));
        $accessor
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->definition, null, $range)
            ->willReturn($identities = Set::of(
                Identity::class,
                new Identity\Identity('uuid1'),
                new Identity\Identity('uuid2')
            ));
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($identities, $request, $this->definition, null, $range)
            ->willReturn(Set::of(Header::class));

        $response = ($this->index)($request, $this->definition);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(206, $response->statusCode()->value());
        $this->assertSame('Partial Content', $response->reasonPhrase()->toString());
        $this->assertSame(
            '{"identities":["uuid1","uuid2"]}',
            $response->body()->toString(),
        );
    }

    public function testThrowWhenProvidingUnwantedIdentity()
    {
        $this->expectException(LogicException::class);

        ($this->index)(
            $this->createMock(ServerRequest::class),
            $this->definition,
            new Identity\Identity(42)
        );
    }
}
