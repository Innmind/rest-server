<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListContentTypeBuilder,
    Response\HeaderBuilder\ListBuilder,
    Formats,
    Format\Format,
    Format\MediaType,
    Identity as IdentityInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Headers\Headers,
    Header,
    Header\Accept,
    Header\AcceptValue,
    Header\Parameter,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\{
    Map,
    Set,
    SetInterface,
};
use PHPUnit\Framework\TestCase;

class ListContentTypeBuilderTest extends TestCase
{
    private $build;

    public function setUp(): void
    {
        $this->build = new ListContentTypeBuilder(
            Formats::of(
                new Format(
                    'json',
                    Set::of(MediaType::class, new MediaType('application/json', 42)),
                    42
                ),
                new Format(
                    'html',
                    Set::of(
                        MediaType::class,
                        new MediaType('text/html', 40),
                        new MediaType('text/xhtml', 0)
                    ),
                    0
                )
            )
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(ListBuilder::class, $this->build);
    }

    public function testBuild()
    {
        $headers = ($this->build)(
            new Set(IdentityInterface::class),
            new ServerRequest(
                $this->createMock(UrlInterface::class),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                Headers::of(
                    new Accept(
                        new AcceptValue(
                            'text',
                            'xhtml',
                            new Map('string', Parameter::class)
                        )
                    )
                )
            ),
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                new Set(Property::class)
            )
        );

        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Content-Type: text/html',
            (string) $headers->current()
        );
    }
}
