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
    Specification\AndFilter
};
use Innmind\Http\{
    Message\ServerRequest\ServerRequest,
    Message\Query\Query,
    Message\Query\Parameter\Parameter,
    Message\Query\Parameter as ParameterInterface,
    Message\Method,
    Message\Environment,
    Message\Cookies,
    Message\Form,
    Message\Files,
    ProtocolVersion,
    Headers
};
use Innmind\Url\UrlInterface;
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    Map,
    Set
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
            new Query(
                (new Map('string', ParameterInterface::class))
                    ->put('foo', new Parameter('foo', 'bar'))
                    ->put('bar', new Parameter('bar', 'baz'))
                    ->put('range', new Parameter('range', [0, 42]))
            ),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
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
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
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
            $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $this->createMock(Headers::class),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            new Query(
                (new Map('string', ParameterInterface::class))
                    ->put('foo', new Parameter('foo', 'bar'))
            ),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $definition = new HttpResource(
            'foo',
            new Identity('uuid'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
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
            $this->createMock(Method::class),
            $this->createMock(ProtocolVersion::class),
            $this->createMock(Headers::class),
            $this->createMock(Readable::class),
            $this->createMock(Environment::class),
            $this->createMock(Cookies::class),
            new Query(new Map('string', ParameterInterface::class)),
            $this->createMock(Form::class),
            $this->createMock(Files::class)
        );
        $definition = new HttpResource(
            'foo',
            new Identity('uuid'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('command'),
            true,
            new Map('string', 'string')
        );
        $builder = new Builder;

        $spec = $builder->buildFrom($request, $definition);
    }
}
