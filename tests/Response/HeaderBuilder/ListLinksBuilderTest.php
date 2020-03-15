<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListLinksBuilder,
    Response\HeaderBuilder\ListBuilder,
    Identity,
    Identity\Identity as Id,
    Definition\Property,
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
use Innmind\Immutable\Set;
use function Innmind\Immutable\first;
use PHPUnit\Framework\TestCase;

class ListLinksBuilderTest extends TestCase
{
    private $build;
    private $directory;

    public function setUp(): void
    {
        $this->build = new ListLinksBuilder(
            new Router(
                Routes::from(
                    $this->directory = require 'fixtures/mapping.php'
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
                Url::of('/foo/bar/'),
                Method::get(),
                new ProtocolVersion(2, 0)
            ),
            $this->directory->definition('image')
        );

        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Link: </top_dir/image/24>; rel="resource", </top_dir/image/42>; rel="resource"',
            first($headers)->toString()
        );
    }

    public function testBuildWithoutIdentities()
    {
        $headers = ($this->build)(
            Set::of(Identity::class),
            new ServerRequest(
                Url::of('/foo/bar/'),
                Method::get(),
                new ProtocolVersion(2, 0)
            ),
            $this->directory->definition('image')
        );

        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertCount(0, $headers);
    }
}
