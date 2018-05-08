<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListLinksBuilder,
    Response\HeaderBuilder\ListBuilder,
    Identity,
    Identity\Identity as Id,
    Definition\Property,
    Definition\Loader\YamlLoader,
    Router,
    Routing\Routes,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Header,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Map,
    MapInterface,
};
use PHPUnit\Framework\TestCase;

class ListLinksBuilderTest extends TestCase
{
    private $build;
    private $directories;

    public function setUp()
    {
        $this->build = new ListLinksBuilder(
            new Router(
                Routes::from(
                    $this->directories = (new YamlLoader)('fixtures/mapping.yml')
                )
            )
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            ListBuilder::class,
            $this->build
        );
    }

    public function testBuild()
    {
        $headers = ($this->build)(
            Set::of(Identity::class, new Id(24), new Id(42)),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class)
            ),
            $this->directories->get('top_dir')->definition('image')
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Link : </top_dir/image/24>; rel="resource", </top_dir/image/42>; rel="resource"',
            (string) $headers->get('Link')
        );
    }

    public function testBuildWithoutIdentities()
    {
        $headers = ($this->build)(
            new Set(Identity::class),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class)
            ),
            $this->directories->get('top_dir')->definition('image')
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
        $this->assertSame(0, $headers->size());
    }
}
