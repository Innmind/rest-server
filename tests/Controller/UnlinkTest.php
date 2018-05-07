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
};

class UnlinkTest extends AbstractTestCase
{
    private $unlink;
    private $gateway;
    private $headerBuilder;

    public function setUp()
    {
        parent::setUp();

        $this->unlink = new Unlink(
            (new Map('string', Gateway::class))->put(
                'foo',
                $this->gateway = $this->createMock(Gateway::class)
            ),
            $this->headerBuilder = $this->createMock(UnlinkBuilder::class),
            new LinkTranslator($this->router)
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
            new LinkTranslator($this->router)
        );
    }

    public function testThrowWhenInvalidGatewayValueType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type MapInterface<string, Innmind\Rest\Server\Gateway>');

        new Unlink(
            new Map('string', 'callable'),
            $this->createMock(UnlinkBuilder::class),
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
        $this
            ->gateway
            ->expects($this->once())
            ->method('resourceUnlinker')
            ->willReturn($unlinker = $this->createMock(ResourceUnlinker::class));
        $unlinker
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $from = new Reference(
                    $this->definition,
                    $identity
                ),
                $tos = (new Map(Reference::class, MapInterface::class))
                    ->put(
                        new Reference(
                            $this->directories->get('top_dir')->definition('image'),
                            new Identity\Identity('42')
                        ),
                        (new Map('string', Parameter::class))
                            ->put('rel', new Parameter\Parameter('rel', 'resource'))
                    )
            );
        $this
            ->headerBuilder
            ->expects($this->once())
            ->method('__invoke')
            ->with($request, $from, $tos)
            ->willReturn(new Map('string', Header::class));

        $response = ($this->unlink)($request, $this->definition, $identity);

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