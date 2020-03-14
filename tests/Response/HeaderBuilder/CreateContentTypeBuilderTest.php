<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateContentTypeBuilder,
    Response\HeaderBuilder\CreateBuilder,
    Formats,
    Format\Format,
    Format\MediaType,
    Identity as IdentityInterface,
    HttpResource as HttpResourceInterface,
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
    SetInterface,
    Set,
};
use PHPUnit\Framework\TestCase;

class CreateContentTypeBuilderTest extends TestCase
{
    private $build;

    public function setUp(): void
    {
        $this->build = new CreateContentTypeBuilder(
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
        $this->assertInstanceOf(CreateBuilder::class, $this->build);
    }

    public function testBuild()
    {
        $headers = ($this->build)(
            $this->createMock(IdentityInterface::class),
            new ServerRequest(
                $this->createMock(UrlInterface::class),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                Headers::of(
                    new Accept(
                        new AcceptValue('text', 'xhtml')
                    )
                )
            ),
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                new Set(Property::class)
            ),
            $this->createMock(HttpResourceInterface::class)
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
