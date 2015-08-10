<?php

namespace Innmind\Rest\Server\Tests\Request;

use Innmind\Rest\Server\Request\Handler;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Storage\Neo4jStorage;
use Innmind\Rest\Server\EventListener\StorageCreateListener;
use Innmind\Rest\Server\Resource;
use Innmind\Rest\Server\Definition\Resource as Definition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Tests\Storage\Bar;
use Innmind\Rest\Server\Registry;
use Innmind\Neo4j\ONM\EntityManagerFactory;
use Innmind\Neo4j\ONM\Configuration;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Yaml;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    protected $h;
    protected $def;
    protected $em;

    public function setUp()
    {
        $dispatcher = new EventDispatcher;
        $accessor = PropertyAccess::createPropertyAccessor();
        $dispatcher->addSubscriber(new StorageCreateListener($accessor));
        $resourceBuilder = new ResourceBuilder(
            $accessor,
            Validation::createValidator(),
            $dispatcher
        );
        $entityBuilder = new EntityBuilder(
            $accessor,
            $dispatcher
        );
        $storages = new Storages;
        $storages->add('neo4j', new Neo4jStorage(
            $this->em = EntityManagerFactory::make(
                [
                    'host' => getenv('CI') ? 'localhost' : 'docker',
                    'username' => 'neo4j',
                    'password' => 'ci',
                ],
                Configuration::create([
                    'cache' => sys_get_temp_dir(),
                    'reader' => 'yaml',
                    'locations' => ['fixtures/neo4j'],
                ], true)
            ),
            $dispatcher,
            $entityBuilder,
            $resourceBuilder
        ));
        $this->h = new Handler($storages, $resourceBuilder);
        $this->def = new Definition('bar');
        $this->def
            ->setId('id')
            ->setStorage('neo4j')
            ->addOption('class', Bar::class)
            ->addProperty(
                (new Property('name'))
                    ->setType('string')
                    ->addAccess('READ')
                    ->addAccess('CREATE')
                    ->addAccess('UPDATE')
            )
            ->addProperty(
                (new Property('id'))
                    ->setType('string')
                    ->addAccess('READ')
            );
    }

    public function testCreateAction()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->assertFalse($r->has('id'));
        $this->assertSame(
            $r,
            $this->h->createAction($r)
        );
        $this->assertTrue($r->has('id'));
        $this->assertTrue(is_string($r->get('id')));
    }

    public function testGetAction()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->h->createAction($r);

        $r2 = $this->h->getAction($this->def, $r->get('id'));
        $this->assertInstanceOf(
            Resource::class,
            $r2
        );
        $this->assertSame(
            $r->get('id'),
            $r2->get('id')
        );
        $this->assertSame(
            $r->get('name'),
            $r2->get('name')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\ResourceNotFoundException
     */
    public function testThrowIfResourceNotFound()
    {
        $this->h->getAction($this->def, 'foo');
    }

    public function testIndexAction()
    {
        $this->em->getConnection()->execute('match (n) delete n');
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->h->createAction($r);
        $this->h->createAction($r);
        $resources = $this->h->indexAction($this->def);
        $this->assertInstanceOf(
            \SplObjectStorage::class,
            $resources
        );
        $this->assertSame(
            2,
            $resources->count()
        );
    }

    public function testUpdateAction()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->h->createAction($r);
        $r->set('name', 'bar');
        $this->assertSame(
            $r,
            $this->h->updateAction($r, $r->get('id'))
        );
        $r2 = $this->h->getAction($this->def, $r->get('id'));
        $this->assertSame(
            'bar',
            $r2->get('name')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\ResourceNotFoundException
     */
    public function testDeleteAction()
    {
        $r = new Resource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->h->createAction($r);
        $this->assertSame(
            null,
            $this->h->deleteAction($this->def, $r->get('id'))
        );
        $this->h->getAction($this->def, $r->get('id'));
    }

    public function testOptionsAction()
    {
        $registry = new Registry;
        $registry->load(Yaml::parse(file_get_contents('fixtures/config.yml')));
        $def = $registry->getCollection('web')->getResource('resource');

        $this->assertEquals(
            [
                'resource' => [
                    'id' => 'uuid',
                    'properties' => [
                        'uri' => [
                            'type' => 'string',
                            'access' =>  ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'scheme' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'host' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'domain' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'tld' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'port' => [
                            'type' => 'int',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'path' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'query' => [
                            'type' => 'string',
                            'access' => ['READ', 'CREATE'],
                            'variants' => [],
                        ],
                        'crawl_date' => [
                            'type' => 'date',
                            'access' => ['READ', 'CREATE', 'UPDATE'],
                            'variants' => ['date']
                        ],
                        'sub_resource' =>[
                            'type' => 'resource',
                            'access' => ['READ'],
                            'variants' => [],
                            'resource' => 'foo',
                        ],
                        'sub_resource_coll' => [
                            'type' => 'array',
                            'access' => ['READ'],
                            'variants' => [],
                            'resource' => 'resource',
                        ],
                    ],
                    'meta' => [
                        'description' => 'Basic representation of a web resource',
                    ],
                ],
            ],
            $this->h->optionsAction($def)
        );
    }
}
