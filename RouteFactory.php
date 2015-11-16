<?php

namespace Innmind\Rest\Server;

use Innmind\Rest\Server\Definition\Resource as ResourceDefinition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteFactory
{
    const NAME_PATTERN = 'innmind_rest_%s_%s_%s';

    /**
     * Build the route for the given action
     *
     * @param ResourceDefinition $resource
     * @param string $action
     *
     * @return Route
     */
    public function makeRoute(ResourceDefinition $resource, $action)
    {
        switch ($action) {
            case 'index':
                $path = '/%s/%s/';
                $method = 'GET';
                break;
            case 'get':
                $path = '/%s/%s/{id}';
                $method = 'GET';
                break;
            case 'create':
                $path = '/%s/%s/';
                $method = 'POST';
                break;
            case 'options':
                $path = '/%s/%s/';
                $method = 'OPTIONS';
                break;
            case 'update':
                $path = '/%s/%s/{id}';
                $method = 'PUT';
                break;
            case 'delete':
                $path = '/%s/%s/{id}';
                $method = 'DELETE';
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'The route action "%s" is not recognized',
                    $action
                ));
        }

        $route = new Route(
            sprintf(
                $path,
                $resource->getCollection(),
                $resource
            ),
            [
                RouteKeys::DEFINITION => sprintf(
                    '%s::%s',
                    $resource->getCollection(),
                    $resource
                ),
                RouteKeys::ACTION => $action,
            ]);
        $route->setMethods($method);

        return $route;
    }

    /**
     * Build all the routes for the given resource
     *
     * @param ResourceDefinition $resource
     *
     * @return RouteCollection
     */
    public function makeRoutes(ResourceDefinition $resource)
    {
        $actions = ['index', 'get', 'create', 'options', 'update', 'delete'];
        $collection = new RouteCollection;

        foreach ($actions as $action) {
            $collection->add(
                $this->makeName($resource, $action),
                $this->makeRoute($resource, $action)
            );
        }

        return $collection;
    }

    /**
     * Make the route name for the given route and action
     *
     * @param ResourceDefinition $resource
     * @param string $action
     *
     * @return string
     */
    public function makeName(ResourceDefinition $resource, $action)
    {
        return sprintf(
            self::NAME_PATTERN,
            $resource->getCollection(),
            $resource,
            $action
        );
    }
}
