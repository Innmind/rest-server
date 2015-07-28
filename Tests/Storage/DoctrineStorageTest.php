<?php

namespace Innmind\Rest\Server\Tests\Storage;

use Innmind\Rest\Server\Storage\DoctrineStorage;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Event\Doctrine\ReadQueryBuilderEvent;
use Innmind\Rest\Server\EventListener\StorageCreateListener;
use Symfony\Component\Validator\Validation;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;

class DoctrineStorageTest extends \PHPUnit_Framework_TestCase
{
    protected $s;
    protected $em;
    protected $d;
    protected $eb;
    protected $rb;
    protected $def;

    public function setUp()
    {
        $conf = new Configuration;
        $conf->setMetadataDriverImpl(new SimplifiedYamlDriver([
            'fixtures/doctrine' => 'Innmind\Rest\Server\Tests\Storage',
        ]));
        $conf->setProxyDir(sys_get_temp_dir());
        $conf->setProxyNamespace('Doctrine\__PROXY__');
        $this->em = EntityManager::create(
            [
                'driver' => 'pdo_sqlite',
                'dbname' => ':memory:',
            ],
            $conf
        );

        $tool = new SchemaTool($this->em);
        $tool->updateSchema([
            $this->em->getClassMetadata(Foo::class),
        ]);

        $this->s = new DoctrineStorage(
            $this->em,
            $this->d = new EventDispatcher,
            $this->eb = new EntityBuilder(
                $accessor = PropertyAccess::createPropertyAccessor(),
                $this->d
            ),
            $this->rb = new ResourceBuilder(
                $accessor,
                Validation::createValidator(),
                $this->d
            )
        );

        $this->def = new ResourceDefinition('foo');
        $this->def
            ->setId('id')
            ->addProperty(
                (new Property('id'))
                    ->setType('int')
            )
            ->addProperty(
                (new Property('name'))
                    ->setType('string')
            )
            ->addOption('class', Foo::class);
        $this->d->addSubscriber(new StorageCreateListener(
            PropertyAccess::createPropertyAccessor()
        ));
    }

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

    public function testDispatchPostCreate()
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.create',
            function(Storage\PostCreateEvent $event) use (&$fired) {
                $fired = true;
                $this->assertInstanceOf(
                    Foo::class,
                    $event->getEntity()
                );
            },
            10
        );
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);

        $this->assertTrue($fired);
    }

    public function testCreate()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);

        $this->assertTrue(is_int($id));
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

    public function testDispatchDoctrineQB()
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.doctrine.read_query_builder',
            function(ReadQueryBuilderEvent $event) use (&$fired) {
                $fired = true;
                $this->assertInstanceOf(
                    QueryBuilder::class,
                    $event->getQueryBuilder()
                );
            }
        );
        $resources = $this->s->read($this->def);

        $this->assertTrue($fired);
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
            function(Storage\PostUpdateEvent $event) use (&$fired) {
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

    public function testDispatchPostUpdate()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.update',
            function(Storage\PostUpdateEvent $event) use (&$fired, $r, $id) {
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
                    Foo::class,
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
            function(Storage\POstDeleteEvent $event) use (&$postFired) {
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

    public function testDispatchPostDelete()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $id = $this->s->create($r);
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.post.delete',
            function(Storage\PostDeleteEvent $event) use (&$fired, $r, $id) {
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
                    Foo::class,
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

/**
 * @ORM\Entity
 */
class Foo {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="string")
     */
    public $name;
}
