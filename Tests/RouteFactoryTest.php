<?php

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\RouteFactory;
use Innmind\Rest\Server\Definition\Resource;
use Innmind\Rest\Server\Definition\Collection;
use Innmind\Rest\Server\RouteKeys;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $f;

    public function setUp()
    {
        $this->f = new RouteFactory;
        $this->d = new Resource('foo');
        $this->d->setCollection(new Collection('bar'));
    }

    /**
     * @dataProvider actions
     */
    public function testMakeName($action)
    {
        $this->assertSame(
            'innmind_rest_bar_foo_' . $action,
            $this->f->makeName($this->d, $action)
        );
    }

    /**
     * @dataProvider actions
     */
    public function testMakeRoute($action, $verb, $path)
    {
        $route = $this->f->makeRoute($this->d, $action);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($path, $route->getPath());
        $this->assertSame([$verb], $route->getMethods());
        $this->assertTrue($route->hasDefault(RouteKeys::DEFINITION));
        $this->assertTrue($route->hasDefault(RouteKeys::ACTION));
        $this->assertSame(
            sprintf('%s::%s', $this->d->getCollection(), $this->d),
            $route->getDefault(RouteKeys::DEFINITION)
        );
        $this->assertSame($action, $route->getDefault(RouteKeys::ACTION));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The route action "foo" is not recognized
     */
    public function testThrowWhenBuildingRouteWithUnknownAction()
    {
        $this->f->makeRoute($this->d, 'foo');
    }

    public function testMakeRoutes()
    {
        $routes = $this->f->makeRoutes($this->d);

        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertSame(6, $routes->count());

        foreach ($this->actions() as $action) {
            $this->assertInstanceOf(
                Route::class,
                $routes->get($this->f->makeName($this->d, $action[0]))
            );
        }
    }

    public function actions()
    {
        return [
            ['index', 'GET', '/bar/foo/'],
            ['get', 'GET', '/bar/foo/{id}'],
            ['create', 'POST', '/bar/foo/'],
            ['options', 'OPTIONS', '/bar/foo/'],
            ['update', 'PUT', '/bar/foo/{id}'],
            ['delete', 'DELETE', '/bar/foo/{id}'],
        ];
    }
}
