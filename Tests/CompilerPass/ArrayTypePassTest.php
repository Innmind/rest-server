<?php

namespace Innmind\Rest\Server\Tests\CompilerPass;

use Innmind\Rest\Server\CompilerPass\ArrayTypePass;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Registry;

class ArrayTypePassTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $resource;
    protected $p;

    public function setUp()
    {
        $this->resource = new Resource('foo');
        $this->resource->addProperty(
            (new Property('bar'))
                ->setType('string')
                ->addAccess('READ')
        );
        $this->resource->setStorage('neo4j');
        $collection = new Collection('baz');
        $collection->addResource($this->resource);
        $this->registry = new Registry;
        $this->registry->addCollection($collection);
        $this->p = new ArrayTypePass;
    }

    public function testProcess()
    {
        $this->resource->addProperty(
            (new Property('foobar'))
                ->setType('array')
                ->addOption('inner_type', 'string')
        );

        $this->assertSame(
            null,
            $this->p->process($this->registry)
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\ConfigurationException
     * @expectedExceptionMessage You must specify an "inner_type" for baz::foo.foobar
     */
    public function testThrowIfNoInnerTypeDefinedOnProperty()
    {
        $this->resource->addProperty(
            (new Property('foobar'))
                ->setType('array')
        );

        $this->p->process($this->registry);
    }
}
