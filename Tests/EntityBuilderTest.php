<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Collection;
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
        $r = new Resource;
        $r->set('foo', 'bar');
        $r->setDefinition(
            (new Definition('foo'))
                ->addOption('class', Foo::class)
        );

        $entity = $this->b->build($r);

        $this->assertInstanceOf(
            Foo::class,
            $entity
        );
    }

    public function testUpdate()
    {
        $r = new Resource;
        $r->set('foo', 'baz');
        $r->setDefinition(
            (new Definition('foo'))
                ->addOption('class', Foo::class)
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
        $def = new Definition('bar');
        $def->setCollection($collection);
        $r = new Resource;
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
        $def = new Definition('bar');
        $def->setCollection($collection);
        $def->addOption('class', Foo::class);
        $r = new Resource;
        $r->setDefinition($def);

        $this->b->build($r, new \stdClass);
    }

    public function testDispatchEvent()
    {
        $fired = false;
        $r = new Resource;
        $r->set('foo', 'bar');
        $r->setDefinition(
            (new Definition('foo'))
                ->addOption('class', Foo::class)
        );
        $this->d->addListener(
            'innmind.rest.server.entity.build',
            function (EntityBuildEvent $event) use (&$fired, $r) {
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
}

class Foo
{
    public $foo;
}
