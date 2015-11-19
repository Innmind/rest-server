<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Event\ResourceBuildEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ResourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $b;
    protected $d;

    public function setUp()
    {
        $this->b = new ResourceBuilder(
            PropertyAccess::createPropertyAccessor(),
            $this->d = new EventDispatcher
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

    public function testDoesntThrowIfUnknownPropertyFromDataObject()
    {
        $d = new ResourceDefinition('bar');
        $d->addProperty(new Property('foo'));

        try {
            $this->b->build(new \stdClass, $d);
        } catch (\Exception $e) {
            $this->fail('It should not throw if property not found');
        }
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
            HttpResourceInterface::class,
            $r
        );
        $this->assertTrue($r->has('bar'));
        $this->assertSame(
            'baz',
            $r->get('bar')
        );
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
            HttpResourceInterface::class,
            $r
        );
        $this->assertTrue($r->has('bar'));
        $this->assertSame(
            ['baz'],
            $r->get('bar')
        );
    }

    public function testDispatchEvent()
    {
        $fired = false;
        $d = new ResourceDefinition('bar');
        $d
            ->setCollection(new Collection('foo'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('int')
            );
        $o = new \stdClass;
        $o->foo = 42;

        $this->d->addListener(
            'innmind.rest.server.resource.build',
            function(ResourceBuildEvent $event) use (&$fired, $d, $o) {
                $fired = true;
                $this->assertSame(
                    $d,
                    $event->getDefinition()
                );
                $this->assertSame(
                    $o,
                    $event->getData()
                );
            }
        );

        $this->b->build($o, $d);
        $this->assertTrue($fired);
    }

    public function testBuildResourceInEvent()
    {
        $this->d->addListener(
            'innmind.rest.server.resource.build',
            function(ResourceBuildEvent $event) {
                $event->setResource(
                    (new HttpResource)
                        ->setDefinition(new ResourceDefinition('foo'))
                        ->set('my', 'own')
                );
            }
        );
        $d = new ResourceDefinition('bar');
        $d
            ->setCollection(new Collection('foo'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('int')
            );
        $o = new \stdClass;
        $o->foo = 42;

        $r = $this->b->build($o, $d);

        $this->assertTrue($r->has('my'));
        $this->assertSame(
            'own',
            $r->get('my')
        );
        $this->assertFalse($r->has('foo'));
    }

    public function testBuildSubResource()
    {
        $d2 = new ResourceDefinition('foo');
        $d2
            ->setCollection($c = new Collection('foo'))
            ->addProperty(
                (new Property('foo'))
                    ->setType('int')
            );
        $d = new ResourceDefinition('bar');
        $d
            ->setCollection($c)
            ->addProperty(
                (new Property('foo'))
                    ->setType('resource')
                    ->addOption('resource', $d2)
            )
            ->addProperty(
                (new Property('bar'))
                    ->setType('array')
                    ->addOption('inner_type', 'resource')
                    ->addOption('resource', $d2)
            );

        $s = new \stdClass;
        $s->foo = 42;
        $o = new \stdClass;
        $o->foo = $s;
        $o->bar = [$s];

        $r = $this->b->build($o, $d);

        $this->assertInstanceOf(
            HttpResourceInterface::class,
            $r->get('foo')
        );
        $this->assertSame(
            42,
            $r->get('foo')->get('foo')
        );
        $this->assertSame(
            42,
            $r->get('bar')[0]->get('foo')
        );
    }

    public function testBuildWithMissingOptionalProperty()
    {
        $def = new ResourceDefinition('foo');
        $def->addProperty(
            (new Property('foo'))
                ->setType('string')
                ->addOption('optional', null)
        );

        $r = $this->b->build(new \stdClass, $def);

        $this->assertInstanceOf(
            HttpResourceInterface::class,
            $r
        );
    }
}
