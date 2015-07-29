<?php

namespace Innmind\Rest\Server\Tests\Storage;

use Innmind\Rest\Server\Storage\Neo4jStorage;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Event\Storage;
use Innmind\Rest\Server\Event\Neo4j\ReadQueryBuilderEvent;
use Innmind\Rest\Server\EventListener\StorageCreateListener;
use Innmind\Neo4j\ONM\EntityManagerFactory;
use Innmind\Neo4j\ONM\Configuration;
use Innmind\Neo4j\ONM\QueryBuilder;
use Symfony\Component\Validator\Validation;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;

class Neo4jStorageTest extends AbstractStorage
{
    protected $s;
    protected $em;
    protected $d;
    protected $eb;
    protected $rb;
    protected $def;

    public function setUp()
    {
        $conf = Configuration::create([
            'cache' => sys_get_temp_dir(),
            'reader' => 'yaml',
            'locations' => ['fixtures/neo4j'],
        ], true);
        $this->em = EntityManagerFactory::make(
            [
                'host' => getenv('CI') ? 'localhost' : 'docker',
                'username' => 'neo4j',
                'password' => 'ci',
            ],
            $conf,
            $this->d = new EventDispatcher
        );

        $this->s = new Neo4jStorage(
            $this->em,
            $this->d,
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
        $this->em->getConnection()->execute('MATCH (n) DELETE n;');

        $this->def = new ResourceDefinition('foo');
        $this->def
            ->setId('id')
            ->addProperty(
                (new Property('id'))
                    ->setType('string')
            )
            ->addProperty(
                (new Property('name'))
                    ->setType('string')
            )
            ->addOption('class', Bar::class);
        $this->d->addSubscriber(new StorageCreateListener(
            PropertyAccess::createPropertyAccessor()
        ));
    }

    public function testDispatchNeo4jQB()
    {
        $fired = false;
        $this->d->addListener(
            'innmind.rest.storage.neo4j.read_query_builder',
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
        parent::testDispatchPostUpdate(Bar::class);
    }

    public function testDispatchPostDelete()
    {
        parent::testDispatchPostDelete(Bar::class);
    }

    public function testDispatchPostCreate()
    {
        parent::testDispatchPostCreate(Bar::class);
    }
}

class Bar {
    public $id;
    public $name;
}
