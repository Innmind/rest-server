<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\RouteLoader;
use Innmind\Rest\Server\Registry;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Property;
use Innmind\Rest\Server\Events;
use Innmind\Rest\Server\Event\RouteEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RouteLoaderTest extends \PHPUnit_Framework_TestCase
{
    protected $r;
    protected $resource;
    protected $registry;

    public function setUp()
    {
        $this->registry = new Registry;
        $collection = new Collection('foo');
        $collection->setStorage('doctrine');
        $resource = new Resource('bar');
        $property = new Property('baz');
        $resource->addProperty($property);
        $collection->addResource($resource);
        $this->registry->addCollection($collection);

        $this->r = new RouteLoader(new EventDispatcher, $this->registry);
        $this->resource = $resource;
    }

    public function testSupports()
    {
        $this->assertTrue($this->r->supports('.', 'innmind_rest'));
        $this->assertTrue($this->r->supports('foo', 'innmind_rest'));
        $this->assertFalse($this->r->supports('.'));
        $this->assertFalse($this->r->supports('.', 'foo'));
    }

    public function testAddListRoute()
    {
        $routes = $this->r->load('.')->all();

        $this->assertTrue(isset($routes['innmind_rest_foo_bar_index']));
        $route = $routes['innmind_rest_foo_bar_index'];

        $this->assertSame(
            '/foo/bar/',
            $route->getPath()
        );
        $this->assertSame(
            ['GET'],
            $route->getMethods()
        );
        $this->assertSame(
            $this->resource,
            $route->getDefault(RouteLoader::RESOURCE_KEY)
        );
        $this->assertSame(
            'index',
            $route->getDefault(RouteLoader::ACTION_KEY)
        );
    }

    public function testAddCreateRoute()
    {
        $routes = $this->r->load('.')->all();

        $this->assertTrue(isset($routes['innmind_rest_foo_bar_create']));
        $route = $routes['innmind_rest_foo_bar_create'];

        $this->assertSame(
            '/foo/bar/',
            $route->getPath()
        );
        $this->assertSame(
            ['POST'],
            $route->getMethods()
        );
        $this->assertSame(
            $this->resource,
            $route->getDefault(RouteLoader::RESOURCE_KEY)
        );
        $this->assertSame(
            'create',
            $route->getDefault(RouteLoader::ACTION_KEY)
        );
    }

    public function testAddGetRoute()
    {
        $routes = $this->r->load('.')->all();

        $this->assertTrue(isset($routes['innmind_rest_foo_bar_get']));
        $route = $routes['innmind_rest_foo_bar_get'];

        $this->assertSame(
            '/foo/bar/{id}',
            $route->getPath()
        );
        $this->assertSame(
            ['GET'],
            $route->getMethods()
        );
        $this->assertSame(
            $this->resource,
            $route->getDefault(RouteLoader::RESOURCE_KEY)
        );
        $this->assertSame(
            'get',
            $route->getDefault(RouteLoader::ACTION_KEY)
        );
    }

    public function testAddUpdateRoute()
    {
        $routes = $this->r->load('.')->all();

        $this->assertTrue(isset($routes['innmind_rest_foo_bar_update']));
        $route = $routes['innmind_rest_foo_bar_update'];

        $this->assertSame(
            '/foo/bar/{id}',
            $route->getPath()
        );
        $this->assertSame(
            ['PUT'],
            $route->getMethods()
        );
        $this->assertSame(
            $this->resource,
            $route->getDefault(RouteLoader::RESOURCE_KEY)
        );
        $this->assertSame(
            'update',
            $route->getDefault(RouteLoader::ACTION_KEY)
        );
    }

    public function testAddDeleteRoute()
    {
        $routes = $this->r->load('.')->all();

        $this->assertTrue(isset($routes['innmind_rest_foo_bar_delete']));
        $route = $routes['innmind_rest_foo_bar_delete'];

        $this->assertSame(
            '/foo/bar/{id}',
            $route->getPath()
        );
        $this->assertSame(
            ['DELETE'],
            $route->getMethods()
        );
        $this->assertSame(
            $this->resource,
            $route->getDefault(RouteLoader::RESOURCE_KEY)
        );
        $this->assertSame(
            'delete',
            $route->getDefault(RouteLoader::ACTION_KEY)
        );
    }

    public function testAddOptionsRoute()
    {
        $routes = $this->r->load('.')->all();

        $this->assertTrue(isset($routes['innmind_rest_foo_bar_options']));
        $route = $routes['innmind_rest_foo_bar_options'];

        $this->assertSame(
            '/foo/bar/',
            $route->getPath()
        );
        $this->assertSame(
            ['OPTIONS'],
            $route->getMethods()
        );
        $this->assertSame(
            $this->resource,
            $route->getDefault(RouteLoader::RESOURCE_KEY)
        );
        $this->assertSame(
            'options',
            $route->getDefault(RouteLoader::ACTION_KEY)
        );
    }

    public function testPrefix()
    {
        $loader = new RouteLoader(new EventDispatcher, $this->registry, '/foo/');
        $routes = $loader->load('.')->all();
        $route = $routes['innmind_rest_foo_bar_index'];

        $this->assertSame(
            '/foo/foo/bar/',
            $route->getPath()
        );
    }

    public function testDispatchEvent()
    {
        $fired = false;
        $d = new EventDispatcher;
        $d->addListener(Events::ROUTE, function($event) use (&$fired) {
            $this->assertInstanceOf(RouteEvent::class, $event);
            $fired = true;
        });
        $loader = new RouteLoader($d, $this->registry);
        $this->assertFalse($fired);
        $loader->load('.');
        $this->assertTrue($fired);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Do not add the "innmind_rest" loader twice
     */
    public function testThrowIfLoaderLoadedTwice()
    {
        $this->r->load('.');
        $this->r->load('.');
    }
}
