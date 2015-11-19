<?php

namespace Innmind\Rest\Server\Test\Controller;

use Innmind\Rest\Server\Controller\ResourceController;
use Innmind\Rest\Server\EventListener\StorageCreateListener;
use Innmind\Rest\Server\ResourceBuilder;
use Innmind\Rest\Server\EntityBuilder;
use Innmind\Rest\Server\Storages;
use Innmind\Rest\Server\Storage\Neo4jStorage;
use Innmind\Rest\Server\Tests\Storage\Bar;
use Innmind\Rest\Server\Definition\ResourceDefinition;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\HttpResourceInterface;
use Innmind\Rest\Server\HttpResource;
use Innmind\Rest\Server\Collection;
use Innmind\Rest\Server\Registry;
use Innmind\Neo4j\ONM\EntityManagerFactory;
use Innmind\Neo4j\ONM\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Yaml\Yaml;

class ResourceControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $c;

    public function setUp()
    {
        $dispatcher = new EventDispatcher;
        $accessor = PropertyAccess::createPropertyAccessor();
        $dispatcher->addSubscriber(new StorageCreateListener($accessor));
        $resourceBuilder = new ResourceBuilder(
            $accessor,
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
        $this->c = new ResourceController($storages);
        $this->def = new ResourceDefinition('bar');
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
        $r = new HttpResource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->assertFalse($r->has('id'));
        $this->assertSame(
            $r,
            $this->c->createAction($r)
        );
        $this->assertTrue($r->has('id'));
        $this->assertTrue(is_string($r->get('id')));
    }

    public function testGetAction()
    {
        $r = new HttpResource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->c->createAction($r);

        $r2 = $this->c->getAction($this->def, $r->get('id'));
        $this->assertInstanceOf(
            HttpResourceInterface::class,
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
        $this->c->getAction($this->def, 'foo');
    }

    public function testIndexAction()
    {
        $this->em->getConnection()->execute('match (n) delete n');
        $r = new HttpResource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->c->createAction($r);
        $this->c->createAction($r);
        $resources = $this->c->indexAction($this->def);
        $this->assertInstanceOf(
            Collection::class,
            $resources
        );
        $this->assertSame(
            2,
            $resources->count()
        );
    }

    public function testUpdateAction()
    {
        $r = new HttpResource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->c->createAction($r);
        $r->set('name', 'bar');
        $this->assertSame(
            $r->get('id'),
            $this->c->updateAction($r, $r->get('id'))->get('id')
        );
        $r2 = $this->c->getAction($this->def, $r->get('id'));
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
        $r = new HttpResource;
        $r
            ->setDefinition($this->def)
            ->set('name', 'foo');
        $this->c->createAction($r);
        $this->assertSame(
            null,
            $this->c->deleteAction($this->def, $r->get('id'))
        );
        $this->c->getAction($this->def, $r->get('id'));
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
                        'uuid' => [
                            'type' => 'string',
                            'access' => ['READ'],
                            'variants' => [],
                        ],
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
                            'optional' => true,
                        ],
                        'sub_resource_coll' => [
                            'type' => 'array',
                            'access' => ['READ'],
                            'variants' => [],
                            'resource' => 'resource',
                            'optional' => true,
                            'inner_type' => 'resource',
                        ],
                    ],
                    'meta' => [
                        'description' => 'Basic representation of a web resource',
                    ],
                ],
            ],
            $this->c->optionsAction($def)
        );
    }
}
