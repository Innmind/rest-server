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
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Query\Query,
    Message\Query\Parameter\Parameter,
    Message\Method,
    Message\Environment,
    Message\Cookies,
    ProtocolVersion,
    Headers,
};
use Innmind\Url\UrlInterface;
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testBuildFrom()
    {
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $this->createMock(Headers::class),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            Query::of(
                new Parameter('foo', 'bar'),
                new Parameter('bar', 'baz'),
                new Parameter('range', [0, 42])
            )
        );
        $definition = HttpResource::rangeable(
            'foo',
            new Identity('uuid'),
            Map::of('string', Property::class)
                (
                    'foo',
                    Property::optional(
                        'foo',
                        new StringType,
                        new Access(Access::READ)
                    )
                )
                (
                    'bar',
                    Property::optional(
                        'bar',
                        new StringType,
                        new Access(Access::READ)
                    )
                ),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('command'),
            new Map('string', 'string')
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

    /**
     * @expectedException Innmind\Rest\Server\Exception\FilterNotApplicable
     * @expectedExceptionMessage foo
     */
    public function testThrowWhenNoPropertyForTheFilter()
    {
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $this->createMock(Headers::class),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            Query::of(new Parameter('foo', 'bar'))
        );
        $definition = HttpResource::rangeable(
            'foo',
            new Identity('uuid'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('command'),
            new Map('string', 'string')
        );
        $build = new Builder;

        $spec = $build($request, $definition);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NoFilterFound
     */
    public function testThrowWhenNoFilterFound()
    {
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $this->createMock(Headers::class),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            new Query
        );
        $definition = HttpResource::rangeable(
            'foo',
            new Identity('uuid'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('command'),
            new Map('string', 'string')
        );
        $build = new Builder;

        $spec = $build($request, $definition);
    }
}
