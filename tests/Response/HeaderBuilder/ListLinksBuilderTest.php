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
    Definition\Types,
    Router,
    Routing\Routes,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Method,
    ProtocolVersion,
    Headers,
    Message\Environment,
    Message\Cookies,
    Message\Query,
    Message\Form,
    Message\Files,
    Header
};
use Innmind\Url\Url;
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    Set,
    Map,
    MapInterface
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
                    $this->directories = (new YamlLoader(new Types))->load(
                        Set::of('string', 'fixtures/mapping.yml')
                    )
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
            (new Set(Identity::class))
                ->add(new Id(24))
                ->add(new Id(42)),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(Method::class),
                $this->createMock(ProtocolVersion::class),
                $this->createMock(Headers::class),
                $this->createMock(Readable::class),
                $this->createMock(Environment::class),
                $this->createMock(Cookies::class),
                $this->createMock(Query::class),
                $this->createMock(Form::class),
                $this->createMock(Files::class)
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
                $this->createMock(ProtocolVersion::class),
                $this->createMock(Headers::class),
                $this->createMock(Readable::class),
                $this->createMock(Environment::class),
                $this->createMock(Cookies::class),
                $this->createMock(Query::class),
                $this->createMock(Form::class),
                $this->createMock(Files::class)
            ),
            $this->directories->get('top_dir')->definition('image')
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
        $this->assertSame(0, $headers->size());
    }
}
