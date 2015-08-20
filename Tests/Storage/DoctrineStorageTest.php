<?php

namespace Innmind\Rest\Server\Tests\Storage;

use Innmind\Rest\Server\Storage\DoctrineStorage;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\ResourceBuilder;
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

class DoctrineStorageTest extends AbstractStorage
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
        $this->s->read($this->def);

        $this->assertTrue($fired);
    }

    public function testDispatchPostUpdate()
    {
        $this->dispatchPostUpdate(Foo::class);
    }

    public function testDispatchPostDelete()
    {
        $this->dispatchPostDelete(Foo::class);
    }

    public function testDispatchPostCreate()
    {
        $this->dispatchPostCreate(Foo::class);
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
