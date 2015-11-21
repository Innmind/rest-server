<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Event\EntityBuildEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EntityBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $b;
    protected $d;

    public function setUp()
    {
        $this->b = new EntityBuilder(
            PropertyAccess::createPropertyAccessor(),
            $this->d = new EventDispatcher
        );
    }

    public function testCreate()
    {
        $r = new HttpResource;
        $r->set('foo', 'bar');
        $r->setDefinition(
            (new ResourceDefinition('foo'))
                ->addOption('class', Foo::class)
                ->addProperty(
                    (new Property('foo'))
                        ->setType('string')
                )
        );

        $entity = $this->b->build($r);

        $this->assertInstanceOf(
            Foo::class,
            $entity
        );
        $this->assertSame(
            'bar',
            $entity->foo
        );
    }

    public function testUpdate()
    {
        $r = new HttpResource;
        $r->set('foo', 'baz');
        $r->setDefinition(
            (new ResourceDefinition('foo'))
                ->addOption('class', Foo::class)
                ->addProperty(
                    (new Property('foo'))
                        ->setType('string')
                )
        );

        $entity = new Foo;
        $entity->foo = 'bar';
        $this->b->build($r, $entity);

        $this->assertSame(
            'baz',
            $entity->foo
        );
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage A "class" must be specified to build an entity for foo::bar
     */
    public function testThrowWhenNoClassSpecified()
    {
        $collection = new Collection('foo');
        $def = new ResourceDefinition('bar');
        $def->setCollection($collection);
        $r = new HttpResource;
        $r->setDefinition($def);

        $this->b->build($r);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage The given entity must be an instance of Innmind\Rest\Server\Tests\Foo
     */
    public function testThrowWhenEntityDoesntMatchDefinedClass()
    {
        $collection = new Collection('foo');
        $def = new ResourceDefinition('bar');
        $def->setCollection($collection);
        $def->addOption('class', Foo::class);
        $r = new HttpResource;
        $r->setDefinition($def);

        $this->b->build($r, new \stdClass);
    }

    public function testDispatchEvent()
    {
        $fired = false;
        $r = new HttpResource;
        $r->set('foo', 'bar');
        $r->setDefinition(
            (new ResourceDefinition('foo'))
                ->addOption('class', Foo::class)
        );
        $this->d->addListener(
            'innmind.rest.server.entity.build',
            function(EntityBuildEvent $event) use (&$fired, $r) {
                $fired = true;
                $this->assertSame(
                    $r,
                    $event->getResource()
                );
                $this->assertInstanceOf(
                    Foo::class,
                    $event->getEntity()
                );
            }
        );

        $this->b->build($r);

        $this->assertTrue($fired);
    }

    public function testCreateSubEntity()
    {
        $d = (new ResourceDefinition('foo'))
            ->addOption('class', Foo::class);
        $d->addProperty(
            (new Property('foo'))
                ->setType('resource')
                ->addOption('resource', $d)
        );
        $sr = new HttpResource;
        $sr->setDefinition($d);
        $r = new HttpResource;
        $r->set('foo', $sr);
        $r->setDefinition($d);

        $entity = $this->b->build($r);

        $this->assertInstanceOf(
            Foo::class,
            $entity
        );
        $this->assertInstanceOf(
            Foo::class,
            $entity->foo
        );
        $this->assertSame(
            null,
            $entity->foo->foo
        );
    }

    public function testCreateSubEntityInArray()
    {
        $d = (new ResourceDefinition('foo'))
            ->addOption('class', Foo::class);
        $d->addProperty(
            (new Property('foo'))
                ->setType('array')
                ->addOption('inner_type', 'resource')
                ->addOption('resource', $d)
        );
        $sr = new HttpResource;
        $sr->setDefinition($d);
        $r = new HttpResource;
        $r->set('foo', [$sr]);
        $r->setDefinition($d);

        $entity = $this->b->build($r);

        $this->assertInstanceOf(
            Foo::class,
            $entity
        );
        $this->assertTrue(is_array($entity->foo));
        $this->assertSame(
            1,
            count($entity->foo)
        );
        $this->assertInstanceOf(
            Foo::class,
            $entity->foo[0]
        );
        $this->assertSame(
            null,
            $entity->foo[0]->foo
        );
    }

    public function testUpdateSubEntity()
    {
        $d = (new ResourceDefinition('foo'))
            ->addOption('class', Foo::class);
        $d
            ->addProperty(
                (new Property('foo'))
                    ->setType('resource')
                    ->addOption('resource', $d)
            )
            ->addProperty(
                (new Property('bar'))
                    ->setType('string')
            );
        $sr = new HttpResource;
        $sr
            ->setDefinition($d)
            ->set('bar', 'baz');
        $r = new HttpResource;
        $r->set('foo', $sr);
        $r->setDefinition($d);

        $entity = new Foo;
        $entity->foo = new Foo;
        $entity->foo->bar = 'bar';

        $this->b->build($r, $entity);

        $this->assertSame(
            'baz',
            $entity->foo->bar
        );
    }

    public function testUpdateSubEntityInArray()
    {
        $d = (new ResourceDefinition('foo'))
            ->addOption('class', Foo::class);
        $d
            ->addProperty(
                (new Property('foo'))
                    ->setType('array')
                    ->addOption('inner_type', 'resource')
                    ->addOption('resource', $d)
            )
            ->addProperty(
                (new Property('bar'))
                    ->setType('string')
            );
        $sr = new HttpResource;
        $sr
            ->setDefinition($d)
            ->set('bar', 'baz');
        $r = new HttpResource;
        $r->set('foo', [$sr]);
        $r->setDefinition($d);

        $entity = new Foo;
        $entity->foo = [new Foo];
        $entity->foo[0]->bar = 'bar';

        $this->b->build($r, $entity);

        $this->assertSame(
            'baz',
            $entity->foo[0]->bar
        );
    }
}

class Foo
{
    public $foo;
    public $bar;
}
