<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListRangeBuilder,
    Response\HeaderBuilder\ListBuilder,
    Identity as IdentityInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    Identity\Identity as Id,
    Request\Range,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\{
    Set,
    Map,
    SetInterface,
};
use PHPUnit\Framework\TestCase;

class ListRangeBuilderTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ListBuilder::class,
            new ListRangeBuilder
        );
    }

    public function testDoesntBuild()
    {
        $build = new ListRangeBuilder;

        $headers = $build(
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                false,
                new Map('string', 'string')
            )
        );

        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertSame(0, $headers->size());
    }

    public function testBuildWithoutRange()
    {
        $build = new ListRangeBuilder;

        $headers = $build(
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
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

        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertSame(1, $headers->size());
        $this->assertSame(
            'Accept-Ranges: resources',
            (string) $headers->current()
        );
    }

    public function testBuildLastRange()
    {
        $build = new ListRangeBuilder;

        $headers = $build(
            Set::of(IdentityInterface::class, new Id(42)),
            $this->createMock(ServerRequest::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            ),
            null,
            new Range(10, 20)
        );

        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertSame(2, $headers->size());
        $this->assertSame(
            'Accept-Ranges: resources',
            (string) $headers->current()
        );
        $headers->next();
        $this->assertSame(
            'Content-Range: resources 10-11/11',
            (string) $headers->current()
        );
    }

    public function testBuildFirstRange()
    {
        $build = new ListRangeBuilder;

        $headers = $build(
            Set::of(
                IdentityInterface::class,
                new Id(42),
                new Id(43),
                new Id(44),
                new Id(45),
                new Id(46),
                new Id(47),
                new Id(48),
                new Id(49),
                new Id(50),
                new Id(51)
            ),
            $this->createMock(ServerRequest::class),
            new HttpResource(
                'foo',
                new Identity('uuid'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('command'),
                true,
                new Map('string', 'string')
            ),
            null,
            new Range(0, 10)
        );

        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertSame(2, $headers->size());
        $this->assertSame(
            'Accept-Ranges: resources',
            (string) $headers->current()
        );
        $headers->next();
        $this->assertSame(
            'Content-Range: resources 0-10/20',
            (string) $headers->current()
        );
    }
}
