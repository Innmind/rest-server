<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Definition\Collection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validation;

class ResourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $b;

    public function setUp()
    {
        $this->b = new ResourceBuilder(
            PropertyAccess::createPropertyAccessor(),
            Validation::createValidator()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You must give a data object in order to build the resource foo
     */
    public function testThrowIfInvalidDataObject()
    {
        $d = new ResourceDefinition('foo');

        $this->b->build([], $d);
    }

    /**
     * @expectedException Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException
     * @expectedExceptionMessage Neither the property "foo" nor one of the methods "getFoo()", "foo()", "isFoo()", "hasFoo()", "__get()" exist and have public access in class "stdClass".
     */
    public function testThrowIfUnknownPropertyFromDataObject()
    {
        $d = new ResourceDefinition('bar');
        $d->addProperty(new Property('foo'));

        $this->b->build(new \stdClass, $d);
    }

    public function testBuild()
    {
        $d = new ResourceDefinition('foo');
        $d->addProperty(
            (new Property('bar'))
                ->setType('string')
        );
        $o = new \stdClass;
        $o->bar = 'baz';

        $r = $this->b->build($o, $d);

        $this->assertInstanceOf(
            Resource::class,
            $r
        );
        $this->assertTrue($r->has('bar'));
        $this->assertSame(
            'baz',
            $r->get('bar')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\PropertyValidationException
     * @expectedExceptionMessage The value at the path "foo" on resource foo::bar does not comply with the type "int" (Original error: This value should be of type int.)
     */
    public function testThrowOnValidationError()
    {
        $d = new ResourceDefinition('bar');
        $d
            ->setCollection(new Collection('foo'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('int')
            );
        $o = new \stdClass;
        $o->foo = '42';

        $this->b->build($o, $d);
    }

    public function testBuildArrayProperty()
    {
        $d = new ResourceDefinition('foo');
        $d->addProperty(
            (new Property('bar'))
                ->setType('array')
                ->addOption('inner_type', 'string')
        );
        $o = new \stdClass;
        $o->bar = ['baz'];

        $r = $this->b->build($o, $d);

        $this->assertInstanceOf(
            Resource::class,
            $r
        );
        $this->assertTrue($r->has('bar'));
        $this->assertSame(
            ['baz'],
            $r->get('bar')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\PropertyValidationException
     * @expectedExceptionMessage The value at the path "foo[0]" on resource foo::bar does not comply with the type "int" (Original error: This value should be of type int.)
     */
    public function testThrowOnValidationErrorInArray()
    {
        $d = new ResourceDefinition('bar');
        $d
            ->setCollection(new Collection('foo'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('array')
                    ->addOption('inner_type', 'int')
            );
        $o = new \stdClass;
        $o->foo = ['42'];

        $this->b->build($o, $d);
    }
}
