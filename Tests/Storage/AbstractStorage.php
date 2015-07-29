<?php

namespace Innmind\Rest\Server\Tests\Storage;

use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Event\Storage;

abstract class AbstractStorage extends \PHPUnit_Framework_TestCase
{
    protected $s;
    protected $em;
    protected $d;
    protected $eb;
    protected $rb;
    protected $def;

    public function testSupports()
    {
        $this->assertTrue($this->s->supports($this->def));
    }

    public function testDoesnSupport()
    {
        $def = clone $this->def;
        $def->addOption('class', self::class);
        $this->assertFalse($this->s->supports($def));
    }

    public function testCreateResourceInPreEvent()
    {
        $this->d->addListener(
            'innmind.rest.storage.pre.create',
            function(Storage\PreCreateEvent $event) {
                $event->setResourceId(42);
            }
        );
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);

        $this->assertSame(42, $id);
    }

    public function testDispatchPostCreate($class)
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.create',
            function(Storage\PostCreateEvent $event) use (&$fired, $class) {
                $fired = true;
                $this->assertInstanceOf(
                    $class,
                    $event->getEntity()
                );
            },
            10
        );
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->s->create($r);

        $this->assertTrue($fired);
    }

    public function testCreate()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);

        $function = sprintf(
            'is_%s',
            $this->def->getProperty('id')->getType()
        );

        $this->assertTrue($function($id));
    }

    public function testDispatchPreRead()
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.pre.read',
            function(Storage\PreReadEvent $event) use (&$fired) {
                $fired = true;
                $event->addResource(
                    (new Resource)
                        ->setDefinition($this->def)
                );
            }
        );
        $resources = $this->s->read($this->def);

        $this->assertTrue($fired);
        $this->assertSame(1, $resources->count());
    }

    public function testDispatchPostRead()
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.read',
            function(Storage\PostReadEvent $event) use (&$fired) {
                $fired = true;
                $event->addResource(
                    (new Resource)
                        ->setDefinition($this->def)
                );
            }
        );
        $resources = $this->s->read($this->def);

        $this->assertTrue($fired);
        $this->assertSame(1, $resources->count());
    }

    public function testRead()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $this->s->create(clone $r);

        $resources = $this->s->read($this->def);

        $this->assertInstanceOf(
            \SplObjectStorage::class,
            $resources
        );
        $this->assertSame(
            2,
            $resources->count()
        );

        $resource = $this->s->read($this->def, $id);

        $this->assertSame(
            1,
            $resource->count()
        );
        $this->assertInstanceOf(
            Resource::class,
            $resource->current()
        );
        $this->assertSame(
            $id,
            $resource->current()->get('id')
        );
        $this->assertSame(
            'foo',
            $resource->current()->get('name')
        );
        $this->assertSame(
            $this->def,
            $resource->current()->getDefinition()
        );
    }

    public function testDispatchPreUpdate()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);

        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.pre.update',
            function(Storage\PreUpdateEvent $event) use (&$fired, $r, $id) {
                $fired = true;
                $this->assertSame(
                    $r,
                    $event->getResource()
                );
                $this->assertSame(
                    $id,
                    $event->getResourceId()
                );
            }
        );
        $r->set('name', 'bar');
        $this->assertSame(
            $this->s,
            $this->s->update($r, $id)
        );
        $this->assertTrue($fired);
        $resource = $this->s->read($this->def, $id);
        $this->assertSame(
            'bar',
            $resource->current()->get('name')
        );
    }

    public function testPreventUpdate()
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.update',
            function() use (&$fired) {
                $fired = true;
            }
        );
        $this->d->addListener(
            'innmind.rest.storage.pre.update',
            function(Storage\PreUpdateEvent $event) use (&$fired) {
                $event->stopPropagation();
            }
        );

        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $r->set('name', 'bar');
        $this->assertSame(
            $this->s,
            $this->s->update($r, $id)
        );
        $this->assertFalse($fired);
    }

    public function testDispatchPostUpdate($class)
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.update',
            function(Storage\PostUpdateEvent $event) use (&$fired, $r, $id, $class) {
                $fired = true;
                $this->assertSame(
                    $r,
                    $event->getResource()
                );
                $this->assertSame(
                    $id,
                    $event->getResourceId()
                );
                $this->assertInstanceOf(
                    $class,
                    $event->getEntity()
                );
            }
        );

        $r->set('name', 'bar');
        $this->assertSame(
            $this->s,
            $this->s->update($r, $id)
        );
        $this->assertTrue($fired);
    }

    public function testDispatchPreDelete()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.pre.delete',
            function(Storage\PreDeleteEvent $event) use (&$fired, $r, $id) {
                $fired = true;
                $this->assertSame(
                    $r->getDefinition(),
                    $event->getDefinition()
                );
                $this->assertSame(
                    $id,
                    $event->getResourceId()
                );
                $event->stopPropagation();
            }
        );
        $postFired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.delete',
            function() use (&$postFired) {
                $postFired = true;
            }
        );

        $this->assertSame(
            $this->s,
            $this->s->delete($this->def, $id)
        );
        $this->assertTrue($fired);
        $this->assertFalse($postFired);
    }

    public function testDispatchPostDelete($class)
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.delete',
            function(Storage\PostDeleteEvent $event) use (&$fired, $r, $id, $class) {
                $fired = true;
                $this->assertSame(
                    $r->getDefinition(),
                    $event->getDefinition()
                );
                $this->assertSame(
                    $id,
                    $event->getResourceId()
                );
                $this->assertInstanceOf(
                    $class,
                    $event->getEntity()
                );
            }
        );

        $this->assertSame(
            $this->s,
            $this->s->delete($this->def, $id)
        );
        $this->assertTrue($fired);
    }

    public function testDelete()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);

        $this->assertSame(
            $this->s,
            $this->s->delete($this->def, $id)
        );
        $this->assertSame(
            0,
            $this->s->read($this->def)->count()
        );
    }
}
