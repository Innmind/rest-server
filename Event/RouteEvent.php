<?php

namespace Innmind\Rest\Server\Event;

use Innmind\Rest\Server\Definition\ResourceDefinition;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteEvent extends Event
{
    protected $routes;
    protected $route;
    protected $resource;

    public function __construct(
        RouteCollection $routes,
        Route $route,
        ResourceDefinition $resource
    ) {
        $this->routes = $routes;
        $this->route = $route;
        $this->resource = $resource;
    }

    /**
     * Return the route collection
     *
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routes;
    }

    /**
     * Return the route that will be added to the collection
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Return the resource the route is bound to
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
