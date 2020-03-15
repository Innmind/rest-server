<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\SpecificationBuilder\Builder;

use Innmind\Rest\Server\{
    SpecificationBuilder\Builder\Builder,
    SpecificationBuilder\Builder as BuilderInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Type\StringType,
    Definition\Access,
    Definition\Gateway,
    Specification\AndFilter,
    Exception\FilterNotApplicable,
    Exception\NoFilterFound,
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Query,
    Message\Query\Parameter,
    Message\Method,
    Message\Environment,
    Message\Cookies,
    ProtocolVersion,
    Headers,
};
use Innmind\Url\Url;
use Innmind\Stream\Readable;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testBuildFrom()
    {
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            new Headers,
            $this->createMock(Readable::class),
            new Environment,
            new Cookies,
            Query::of(
                new Parameter('foo', 'bar'),
                new Parameter('bar', 'baz'),
                new Parameter('range', [0, 42])
            )
        );
        $definition = HttpResource::rangeable(
            'foo',
            new Gateway('command'),
            new Identity('uuid'),
            Set::of(
                Property::class,
                Property::optional(
                    'foo',
                    new StringType,
                    new Access(Access::READ)
                ),
                Property::optional(
                    'bar',
                    new StringType,
                    new Access(Access::READ)
                )
            )
        );
        $build = new Builder;

        $this->assertInstanceOf(BuilderInterface::class, $build);
        $spec = $build($request, $definition);
        $this->assertInstanceOf(AndFilter::class, $spec);
        $this->assertSame('foo', $spec->left()->property());
        $this->assertSame('bar', $spec->left()->value());
        $this->assertSame('bar', $spec->right()->property());
        $this->assertSame('baz', $spec->right()->value());
    }

    public function testThrowWhenNoPropertyForTheFilter()
    {
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            new Headers,
            $this->createMock(Readable::class),
            new Environment,
            new Cookies,
            Query::of(new Parameter('foo', 'bar'))
        );
        $definition = HttpResource::rangeable(
            'foo',
            new Gateway('command'),
            new Identity('uuid'),
            Set::of(Property::class)
        );
        $build = new Builder;

        $this->expectException(FilterNotApplicable::class);
        $this->expectExceptionMessage('foo');

        $spec = $build($request, $definition);
    }

    public function testThrowWhenNoFilterFound()
    {
        $request = new ServerRequest(
            Url::of('http://example.com'),
            Method::get(),
            new ProtocolVersion(2, 0),
            new Headers,
            $this->createMock(Readable::class),
            new Environment,
            new Cookies,
            new Query
        );
        $definition = HttpResource::rangeable(
            'foo',
            new Gateway('command'),
            new Identity('uuid'),
            Set::of(Property::class)
        );
        $build = new Builder;

        $this->expectException(NoFilterFound::class);

        $spec = $build($request, $definition);
    }
}
