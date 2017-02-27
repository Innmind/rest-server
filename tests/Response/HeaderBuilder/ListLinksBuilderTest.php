<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListLinksBuilder,
    Response\HeaderBuilder\ListBuilderInterface,
    IdentityInterface,
    Identity as Id,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\MethodInterface,
    ProtocolVersionInterface,
    HeadersInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\QueryInterface,
    Message\FormInterface,
    Message\FilesInterface,
    Header\HeaderInterface
};
use Innmind\Url\Url;
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\{
    Set,
    Map,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class ListLinksBuilderTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ListBuilderInterface::class,
            new ListLinksBuilder
        );
    }

    public function testBuild()
    {
        $builder = new ListLinksBuilder;

        $headers = $builder->build(
            (new Set(IdentityInterface::class))
                ->add(new Id(24))
                ->add(new Id(42)),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(MethodInterface::class),
                $this->createMock(ProtocolVersionInterface::class),
                $this->createMock(HeadersInterface::class),
                $this->createMock(StreamInterface::class),
                $this->createMock(EnvironmentInterface::class),
                $this->createMock(CookiesInterface::class),
                $this->createMock(QueryInterface::class),
                $this->createMock(FormInterface::class),
                $this->createMock(FilesInterface::class)
            ),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            )
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Link : </foo/bar/24>; rel="resource", </foo/bar/42>; rel="resource"',
            (string) $headers->get('Link')
        );
    }

    public function testBuildWithoutIdentities()
    {
        $builder = new ListLinksBuilder;

        $headers = $builder->build(
            new Set(IdentityInterface::class),
            new ServerRequest(
                Url::fromString('/foo/bar/'),
                $this->createMock(MethodInterface::class),
                $this->createMock(ProtocolVersionInterface::class),
                $this->createMock(HeadersInterface::class),
                $this->createMock(StreamInterface::class),
                $this->createMock(EnvironmentInterface::class),
                $this->createMock(CookiesInterface::class),
                $this->createMock(QueryInterface::class),
                $this->createMock(FormInterface::class),
                $this->createMock(FilesInterface::class)
            ),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            )
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
        $this->assertSame(0, $headers->size());
    }
}
