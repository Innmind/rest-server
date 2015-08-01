<?php

namespace Innmind\Rest\Server\Tests\CompilerPass;

use Innmind\Rest\Server\CompilerPass\AccessPass;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Registry;

class AccessPassTest extends \PHPUnit_Framework_TestCase
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
        $this->p = new AccessPass;
    }

    public function testProcess()
    {
        $this->assertSame(
            null,
            $this->p->process($this->registry)
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\UnknownPropertyAccessException
     * @expectedExceptionMessage You must specify at least on access for baz::foo.foobar
     */
    public function testThrowIfNoAccessDefinedOnProperty()
    {
        $this->resource->addProperty(
            (new Property('foobar'))
                ->setType('string')
        );

        $this->p->process($this->registry);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\UnknownPropertyAccessException
     * @expectedExceptionMessage The access "foo" is invalid for baz::foo.foobar
     */
    public function testThrowIfUnknownAccessType()
    {
        $this->resource->addProperty(
            (new Property('foobar'))
                ->setType('string')
                ->addAccess('foo')
        );

        $this->p->process($this->registry);
    }
}
