<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListRangeBuilder,
    Response\HeaderBuilder\ListBuilderInterface,
    IdentityInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    Identity as Id,
    Request\Range
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface
};
use Innmind\Immutable\{
    Set,
    Map,
    Collection,
    MapInterface
};

class ListRangeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ListBuilderInterface::class,
            new ListRangeBuilder
        );
    }

    public function testDoesntBuild()
    {
        $builder = new ListRangeBuilder;

        $headers = $builder->build(
            new Set(IdentityInterface::class),
            $this->getMock(ServerRequestInterface::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                false
            )
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
        $this->assertSame(0, $headers->size());
    }

    public function testBuildWithoutRange()
    {
        $builder = new ListRangeBuilder;

        $headers = $builder->build(
            new Set(IdentityInterface::class),
            $this->getMock(ServerRequestInterface::class),
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
            'Accept-Ranges : resources',
            (string) $headers->get('Accept-Ranges')
        );
    }

    public function testBuildLastRange()
    {
        $builder = new ListRangeBuilder;

        $headers = $builder->build(
            (new Set(IdentityInterface::class))
                ->add(new Id(42)),
            $this->getMock(ServerRequestInterface::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                true
            ),
            null,
            new Range(10, 20)
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
        $this->assertSame(2, $headers->size());
        $this->assertSame(
            'Accept-Ranges : resources',
            (string) $headers->get('Accept-Ranges')
        );
        $this->assertSame(
            'Content-Range : resources 10-11/11',
            (string) $headers->get('Content-Range')
        );
    }

    public function testBuildFirstRange()
    {
        $builder = new ListRangeBuilder;

        $headers = $builder->build(
            (new Set(IdentityInterface::class))
                ->add(new Id(42))
                ->add(new Id(43))
                ->add(new Id(44))
                ->add(new Id(45))
                ->add(new Id(46))
                ->add(new Id(47))
                ->add(new Id(48))
                ->add(new Id(49))
                ->add(new Id(50))
                ->add(new Id(51)),
            $this->getMock(ServerRequestInterface::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('command'),
                true
            ),
            null,
            new Range(0, 10)
        );

        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
        $this->assertSame(2, $headers->size());
        $this->assertSame(
            'Accept-Ranges : resources',
            (string) $headers->get('Accept-Ranges')
        );
        $this->assertSame(
            'Content-Range : resources 0-10/20',
            (string) $headers->get('Content-Range')
        );
    }
}
