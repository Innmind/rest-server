<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Unlink,
    Controller,
    Identity,
    Gateway,
    Response\HeaderBuilder\UnlinkBuilder,
    ResourceUnlinker,
    Reference,
    Translator\LinkTranslator,
    Link,
    Link\Parameter,
    Definition\Locator,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Header,
    Header\Link as LinkHeader,
    Header\LinkValue,
    Headers\Headers,
    Exception\Http\BadRequest,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    MapInterface,
    Map,
    Set,
};

class UnlinkTest extends AbstractTestCase
{
    private $unlink;
    private $gateway;
    private $headerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->unlink = new Unlink(
            Map::of('string', Gateway::class)(
                'foo',
                $this->gateway = $this->createMock(Gateway::class)
            ),
            $this->headerBuilder = $this->createMock(UnlinkBuilder::class),
            new LinkTranslator($this->router),
            new Locator($this->directory)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            $this->unlink
        );
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Unlink(
            new Map('int', Gateway::class),
            $this->createMock(UnlinkBuilder::class),
            new LinkTranslator($this->router),
            new Locator($this->directory)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Unlink(
            new Map('string', 'callable'),
            $this->createMock(UnlinkBuilder::class),
            new LinkTranslator($this->router),
            new Locator($this->directory)
        );
    }

    public function testInvokation()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new LinkHeader(
                    new LinkValue(
                        Url::fromString('/top_dir/image/42'),
                        'resource'
                    )
                )
            ));
        $identity = $this->createMock(Identity::class);
        $from = new Reference(
            $this->definition,
            $identity
        );
        $link = new Link(
            new Reference(
                $this->directory->definition('image'),
                new Identity\Identity('42')
            ),
            'resource'
        );

        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceUnlinker')
            ->willReturn($unlinker = $this->createMock(ResourceUnlinker::class));
        $unlinker
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $from,
                $this->callback(static function($value) use ($link): bool {
                    return $value->reference()->definition() === $link->reference()->definition() &&
                        $value->reference()->identity()->value() === $link->reference()->identity()->value() &&
                        $value->relationship() === $link->relationship();
                })
            );
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $request,
                $from,
                $this->callback(static function($value) use ($link): bool {
                    return $value->reference()->definition() === $link->reference()->definition() &&
                        $value->reference()->identity()->value() === $link->reference()->identity()->value() &&
                        $value->relationship() === $link->relationship();
                })
            )
            ->willReturn(new Set(Header::class));

        $response = ($this->unlink)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertSame('', (string) $response->body());
    }

    public function testThrowWhenLinkNotAccepted()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new LinkHeader(
                    new LinkValue(
                        Url::fromString('/top_dir/image/42'),
                        'resource'
                    )
                )
            ));
        $identity = $this->createMock(Identity::class);
        $definition = $this->directory->definition('image');
        $from = new Reference(
            $definition,
            $identity
        );
        $link = new Link(
            new Reference(
                $this->directory->definition('image'),
                new Identity\Identity('42')
            ),
            'resource'
        );

        $this
            ->gateway
            ->expects($this->never())
            ->method('resourceUnlinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        // throws because of the relationship is not "alternate"
        ($this->unlink)($request, $definition, $identity);
    }

    public function testThrowWhenNoLinkHeader()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of());
        $identity = $this->createMock(Identity::class);
        $this
            ->gateway
            ->expects($this->never())
            ->method('resourceUnlinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        ($this->unlink)($request, $this->definition, $identity);
    }

    public function testThrowWhenLinkinkToUnknownResource()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new LinkHeader(
                    new LinkValue(
                        Url::fromString('/foo/image/42'),
                        'resource'
                    )
                )
            ));
        $identity = $this->createMock(Identity::class);
        $this
            ->gateway
            ->expects($this->never())
            ->method('resourceUnlinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        ($this->unlink)($request, $this->definition, $identity);
    }
}
