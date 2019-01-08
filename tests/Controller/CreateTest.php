<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Create,
    Controller,
    Identity\Identity,
    Gateway,
    ResourceCreator,
    Serializer\RequestDecoder,
    Serializer\Encoder,
    Serializer\Normalizer\Identity as IdentityNormalizer,
    Serializer\Denormalizer\HttpResource as ResourceDenormalizer,
    Response\HeaderBuilder\CreateBuilder,
    Exception\LogicException,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\ContentType,
    Header\ContentTypeValue,
};
use Innmind\Filesystem\Stream\StringStream;
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class CreateTest extends AbstractTestCase
{
    private $create;
    private $gateway;
    private $headerBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->create = new Create(
            new RequestDecoder\Json,
            new Encoder\Json,
            new IdentityNormalizer,
            new ResourceDenormalizer,
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
            $this->headerBuilder = $this->createMock(CreateBuilder::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            $this->create
        );
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 4 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Create(
            new RequestDecoder\Json,
            new Encoder\Json,
            new IdentityNormalizer,
            new ResourceDenormalizer,
            new Map('int', Gateway::class),
            $this->createMock(CreateBuilder::class)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 4 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Create(
            new RequestDecoder\Json,
            new Encoder\Json,
            new IdentityNormalizer,
            new ResourceDenormalizer,
            new Map('string', 'callable'),
            $this->createMock(CreateBuilder::class)
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
                ),
                new ContentType(
                    new ContentTypeValue('application', 'json')
                )
            ));
        $request
            ->expects($this->any())
            ->method('body')
            ->willReturn(new StringStream('{"resource":{"url":"example.com"}}'));
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceCreator')
            ->willReturn($resourceCreator = $this->createMock(ResourceCreator::class));
        $resourceCreator
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->definition,
                $this->callback(static function($resource): bool {
                    return $resource->property('url')->value() === 'example.com';
                })
            )
            ->willReturn($identity = new Identity('some uuid'));
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($identity, $request, $this->definition)
            ->willReturn(new Set(Header::class));

        $response = ($this->create)($request, $this->definition);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(201, $response->statusCode()->value());
        $this->assertSame('Created', (string) $response->reasonPhrase());
        $this->assertSame(
            '{"identity":"some uuid"}',
            (string) $response->body()
        );
    }

    public function testThrowWhenProvidingUnwantedIdentity()
    {
        $this->expectException(LogicException::class);

        ($this->create)(
            $this->createMock(ServerRequest::class),
            $this->definition,
            new Identity(42)
        );
    }
}
