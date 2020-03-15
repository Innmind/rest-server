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
use Innmind\Immutable\Set;
use function Innmind\Immutable\{
    first,
    unwrap,
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
            Set::of(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            new HttpResource(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );

        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertCount(0, $headers);
    }

    public function testBuildWithoutRange()
    {
        $build = new ListRangeBuilder;

        $headers = $build(
            Set::of(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );

        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertCount(1, $headers);
        $this->assertSame(
            'Accept-Ranges: resources',
            first($headers)->toString()
        );
    }

    public function testBuildLastRange()
    {
        $build = new ListRangeBuilder;

        $headers = $build(
            Set::of(IdentityInterface::class, new Id(42)),
            $this->createMock(ServerRequest::class),
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            ),
            null,
            new Range(10, 20)
        );

        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertCount(2, $headers);
        $headers = unwrap($headers);
        $this->assertSame(
            'Accept-Ranges: resources',
            \current($headers)->toString(),
        );
        \next($headers);
        $this->assertSame(
            'Content-Range: resources 10-11/11',
            \current($headers)->toString(),
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
            HttpResource::rangeable(
                'foo',
                new Gateway('command'),
                new Identity('uuid'),
                Set::of(Property::class)
            ),
            null,
            new Range(0, 10)
        );

        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
        $this->assertCount(2, $headers);
        $headers = unwrap($headers);
        $this->assertSame(
            'Accept-Ranges: resources',
            \current($headers)->toString(),
        );
        \next($headers);
        $this->assertSame(
            'Content-Range: resources 0-10/20',
            \current($headers)->toString(),
        );
    }
}
