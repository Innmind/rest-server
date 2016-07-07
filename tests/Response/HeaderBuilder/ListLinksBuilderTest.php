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
    MapInterface,
    Collection
};

class ListLinksBuilderTest extends \PHPUnit_Framework_TestCase
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
                $this->getMock(MethodInterface::class),
                $this->getMock(ProtocolVersionInterface::class),
                $this->getMock(HeadersInterface::class),
                $this->getMock(StreamInterface::class),
                $this->getMock(EnvironmentInterface::class),
                $this->getMock(CookiesInterface::class),
                $this->getMock(QueryInterface::class),
                $this->getMock(FormInterface::class),
                $this->getMock(FilesInterface::class)
            ),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                true
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
}
