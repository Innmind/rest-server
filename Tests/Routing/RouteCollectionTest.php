<?php

namespace Innmind\Rest\Server\Tests\Routing;

use Innmind\Rest\Server\Routing\RouteCollection;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Collection;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $r;
    protected $c;

    public function setUp()
    {
        $this->r = (new Resource('foo'))
            ->setCollection(new Collection('bar'));
        $this->c = new RouteCollection($this->r);
    }

    public function testGetIndex()
    {
        $this->assert('/bar/foo/', 'index', 'GET');
    }

    public function testGetCreate()
    {
        $this->assert('/bar/foo/', 'create', 'POST');
    }

    public function testGetOptions()
    {
        $this->assert('/bar/foo/', 'options', 'OPTIONS');
    }

    public function testGetGet()
    {
        $this->assert('/bar/foo/{id}', 'get', 'GET');
    }

    public function testGetUpdate()
    {
        $this->assert('/bar/foo/{id}', 'update', 'PUT');
    }

    public function testGetDelete()
    {
        $this->assert('/bar/foo/{id}', 'delete', 'DELETE');
    }

    protected function assert($path, $action, $method)
    {
        list($collection, $resource) = explode('/', ltrim($path, '/'));
        $r = $this->c->get(sprintf(
            'innmind_rest_%s_%s_%s',
            $collection,
            $resource,
            $action
        ));

        $this->assertSame(
            $path,
            $r->getPath()
        );
        $this->assertSame(
            [$method],
            $r->getMethods()
        );
        $this->assertSame(
            $this->r,
            $r->getDefault(RouteCollection::RESOURCE_KEY)
        );
        $this->assertSame(
            $action,
            $r->getDefault(RouteCollection::ACTION_KEY)
        );
    }
}
