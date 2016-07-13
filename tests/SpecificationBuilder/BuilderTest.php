<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\SpecificationBuilder;

use Innmind\Rest\Server\{
    SpecificationBuilder\Builder,
    SpecificationBuilder\BuilderInterface,
    Definition\HttpResource,
    Definition\Identity,
    Definition\Property,
    Definition\Type\StringType,
    Definition\Access,
    Definition\Gateway,
    Specification\AndFilter
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Query,
    Message\Query\Parameter,
    Message\Query\ParameterInterface,
    Message\MethodInterface,
    Message\EnvironmentInterface,
    Message\CookiesInterface,
    Message\FormInterface,
    Message\FilesInterface,
    ProtocolVersionInterface,
    HeadersInterface
};
use Innmind\Url\UrlInterface;
use Innmind\Filesystem\StreamInterface;
use Innmind\Immutable\{
    Map,
    Set,
    Collection
};

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildFrom()
    {
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(MethodInterface::class),
            $this->createMock(ProtocolVersionInterface::class),
            $this->createMock(HeadersInterface::class),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            new Query(
                (new Map('string', ParameterInterface::class))
                    ->put('foo', new Parameter('foo', 'bar'))
                    ->put('bar', new Parameter('bar', 'baz'))
                    ->put('range', new Parameter('range', [0, 42]))
            ),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $definition = new HttpResource(
            'foo',
            new Identity('uuid'),
            (new Map('string', Property::class))
                ->put(
                    'foo',
                    new Property(
                        'foo',
                        new StringType,
                        new Access(
                            (new Set('string'))->add(Access::READ)
                        ),
                        new Set('string'),
                        true
                    )
                )
                ->put(
                    'bar',
                    new Property(
                        'bar',
                        new StringType,
                        new Access(
                            (new Set('string'))->add(Access::READ)
                        ),
                        new Set('string'),
                        true
                    )
                ),
            new Collection([]),
            new Collection([]),
            new Gateway('command'),
            true,
            new Map('string', 'string')
        );
        $builder = new Builder;

        $this->assertInstanceOf(BuilderInterface::class, $builder);
        $spec = $builder->buildFrom($request, $definition);
        $this->assertInstanceOf(AndFilter::class, $spec);
        $this->assertSame('foo', $spec->left()->property());
        $this->assertSame('bar', $spec->left()->value());
        $this->assertSame('bar', $spec->right()->property());
        $this->assertSame('baz', $spec->right()->value());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\FilterNotApplicableException
     * @expectedExceptionMessage foo
     */
    public function testThrowWhenNoPropertyForTheFilter()
    {
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(MethodInterface::class),
            $this->createMock(ProtocolVersionInterface::class),
            $this->createMock(HeadersInterface::class),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            new Query(
                (new Map('string', ParameterInterface::class))
                    ->put('foo', new Parameter('foo', 'bar'))
            ),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $definition = new HttpResource(
            'foo',
            new Identity('uuid'),
            new Map('string', Property::class),
            new Collection([]),
            new Collection([]),
            new Gateway('command'),
            true,
            new Map('string', 'string')
        );
        $builder = new Builder;

        $spec = $builder->buildFrom($request, $definition);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NoFilterFoundException
     */
    public function testThrowWhenNoFilterFound()
    {
        $request = new ServerRequest(
            $this->createMock(UrlInterface::class),
            $this->createMock(MethodInterface::class),
            $this->createMock(ProtocolVersionInterface::class),
            $this->createMock(HeadersInterface::class),
            $this->createMock(StreamInterface::class),
            $this->createMock(EnvironmentInterface::class),
            $this->createMock(CookiesInterface::class),
            new Query(new Map('string', ParameterInterface::class)),
            $this->createMock(FormInterface::class),
            $this->createMock(FilesInterface::class)
        );
        $definition = new HttpResource(
            'foo',
            new Identity('uuid'),
            new Map('string', Property::class),
            new Collection([]),
            new Collection([]),
            new Gateway('command'),
            true,
            new Map('string', 'string')
        );
        $builder = new Builder;

        $spec = $builder->buildFrom($request, $definition);
    }
}
