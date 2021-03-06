<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Controller;

use Innmind\Rest\Server\{
    Controller\Link as LinkController,
    Controller,
    Identity,
    Gateway,
    Response\HeaderBuilder\LinkBuilder,
    ResourceLinker,
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
    Headers,
    ProtocolVersion,
    Exception\Http\BadRequest,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    Set,
};

class LinkTest extends AbstractTestCase
{
    private $link;
    private $gateway;
    private $headerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->link = new LinkController(
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
            $this->headerBuilder = $this->createMock(LinkBuilder::class),
            new LinkTranslator($this->router),
            new Locator($this->directory)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            Controller::class,
            $this->link
        );
    }

    public function testThrowWhenInvalidGatewayKeyType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new LinkController(
            Map::of('int', Gateway::class),
            $this->createMock(LinkBuilder::class),
            new LinkTranslator($this->router),
            new Locator($this->directory)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type Map<string, Innmind\Rest\Server\Gateway>');

        new LinkController(
            Map::of('string', 'callable'),
            $this->createMock(LinkBuilder::class),
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
                        Url::of('/top_dir/image/42'),
                        'resource'
                    )
                )
            ));
        $request
            ->expects($this->any())
            ->method('protocolVersion')
            ->willReturn(new ProtocolVersion(2, 0));
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
            ->method('resourceLinker')
            ->willReturn($linker = $this->createMock(ResourceLinker::class));
        $linker
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
            ->willReturn(Set::of(Header::class));

        $response = ($this->link)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', $response->reasonPhrase()->toString());
        $this->assertSame('', $response->body()->toString());
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
                        Url::of('/top_dir/image/42'),
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
            ->method('resourceLinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        // throws because of the relationship is not "alternate"
        ($this->link)($request, $definition, $identity);
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
            ->method('resourceLinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        ($this->link)($request, $this->definition, $identity);
    }

    public function testThrowWhenLinkingToUnknownResource()
    {
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new LinkHeader(
                    new LinkValue(
                        Url::of('/foo/image/42'),
                        'resource'
                    )
                )
            ));
        $identity = $this->createMock(Identity::class);
        $this
            ->gateway
            ->expects($this->never())
            ->method('resourceLinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        ($this->link)($request, $this->definition, $identity);
    }
}
