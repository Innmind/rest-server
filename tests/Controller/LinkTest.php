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

class LinkTest extends AbstractTestCase
{
    private $link;
    private $gateway;
    private $headerBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->link = new LinkController(
            Map::of('string', Gateway::class)
                ('foo', $this->gateway = $this->createMock(Gateway::class)),
            $this->headerBuilder = $this->createMock(LinkBuilder::class),
            new LinkTranslator($this->router)
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
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new LinkController(
            new Map('int', Gateway::class),
            $this->createMock(LinkBuilder::class),
            new LinkTranslator($this->router)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new LinkController(
            new Map('string', 'callable'),
            $this->createMock(LinkBuilder::class),
            new LinkTranslator($this->router)
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
            new Parameter\Parameter('rel', 'resource')
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
                        $value->get('rel')->value() === $link->get('rel')->value();
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
                        $value->get('rel')->value() === $link->get('rel')->value();
                })
            )
            ->willReturn(new Set(Header::class));

        $response = ($this->link)($request, $this->definition, $identity);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(204, $response->statusCode()->value());
        $this->assertSame('No Content', (string) $response->reasonPhrase());
        $this->assertSame('', (string) $response->body());
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
            ->method('resourceLinker');
        $this
            ->headerBuilder
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(BadRequest::class);

        ($this->link)($request, $this->definition, $identity);
    }
}
