<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Update,
    Controller,
    Gateway,
    Identity,
    Response\HeaderBuilder\UpdateBuilder,
    ResourceUpdater,
    Serializer\RequestDecoder\Json,
    Serializer\Denormalizer\HttpResource as ResourceDenormalizer,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Headers,
    Header,
    Header\ContentType,
    Header\ContentTypeValue,
    ProtocolVersion,
};
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\{
    Map,
    Set,
};

class UpdateTest extends AbstractTestCase
{
    private $update;
    private $gateway;
    private $headerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->update = new Update(
            new Json,
            $this->format,
            new ResourceDenormalizer,
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
            $this->headerBuilder = $this->createMock(UpdateBuilder::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Controller::class, $this->update);
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Update(
            new Json,
            $this->format,
            new ResourceDenormalizer,
            Map::of('int', Gateway::class),
            $this->createMock(UpdateBuilder::class)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new Update(
            new Json,
            $this->format,
            new ResourceDenormalizer,
            Map::of('string', 'callable'),
            $this->createMock(UpdateBuilder::class)
        );
    }

    public function testInvokation()
    {
        $request = $this->createMock(ServerRequest::class);
        $identity = $this->createMock(Identity::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new ContentType(
                    new ContentTypeValue('application', 'json')
                )
            ));
        $request
            ->expects($this->any())
            ->method('body')
            ->willReturn(Stream::ofContent('{"resource":{"url":"example.com"}}'));
        $request
            ->expects($this->any())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceUpdater')
            ->willReturn($updater = $this->createMock(ResourceUpdater::class));
        $updater
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->definition,
                $identity,
                $this->callback(static function($resource): bool {
                    return $resource->property('url')->value() === 'example.com';
                })
            );
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $this->definition, $identity)
            ->willReturn(Set::of(Header::class));

        $response = ($this->update)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', $response->reasonPhrase()->toString());
        $this->assertSame('', $response->body()->toString());
    }
}
